<?php

namespace App\Http\Controllers\Training\Skills;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\Skills\SubmitApplication;
use App\Logger;
use App\Mail\Training\Skills\ApplicationProcessed;
use App\Mail\Training\Skills\ApplicationSubmitted;
use App\Models\Training\Skills\Application;
use App\Models\Training\Skills\Skill;
use App\Models\Users\User;
use bnjns\LaravelNotifications\Facades\Notify;
use bnjns\WebDevTools\Laravel\Traits\ChecksPaginationPage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Validator;

class ApplicationController extends Controller
{
    use ChecksPaginationPage;

    /**
     * ApplicationController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * View the form to apply for a new skill level.
     *
     * @param int|null $id
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function form($id = null)
    {
        $this->authorize('apply', Application::class);

        $levels = $this->determineSelectableApplicationSkillLevels($skill = $id === null ? null : Skill::find($id), $user = request()->user());

        if (count(array_filter($levels)) == 0) {
            Notify::warning('There are no levels left to apply for');
            return redirect()->route('training.skill.index');
        }

        if ($skill && $user->getSkillLevel($skill) === 3) {
            Notify::warning('You are already ' . Skill::LEVEL_NAMES[3] . ' for this skill');
            return redirect()->route('training.skill.view', ['id' => $skill->id]);
        }

        if ($skill && $user->hasApplicationPending($skill)) {
            Notify::warning('You already have an application pending for this skill');
            return redirect()->route('training.skill.view', ['id' => $skill->id]);
        }

        return view('training.skills.applications.apply')->with([
            'skill'           => $id === null ? null : $skill,
            'AvailableLevels' => array_keys(array_filter($levels)),
        ]);
    }

    /**
     * Process the form and submit the application.
     *
     * @param \App\Http\Requests\Training\Skills\SubmitApplication $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function apply(SubmitApplication $request)
    {
        $this->authorize('apply', Application::class);

        // Create the application
        $skill    = Skill::find($request->get('skill_id'));
        $user     = $request->user();
        $application = Application::create([
            'skill_id'        => $skill->id,
            'user_id'         => $user->id,
            'proposed_level'  => clean($request->get('level')),
            'reasoning'       => clean($request->get('reasoning')),
            'date'            => Carbon::now(),
            'awarded_level'   => null,
            'awarded_by'      => null,
            'awarded_comment' => null,
            'awarded_date'    => null,
        ]);

        // Email the T&S officer
        Mail::to('training@bts-crew.com')
            ->queue(new ApplicationSubmitted($skill, $application, $user));

        Notify::success('Application submitted');
        return redirect()->route('training.skill.index');
    }

    /**
     * View the application index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $unawarded = Application::notAwarded()
                                ->orderBy('date', 'ASC')
                                ->get();
        $awarded   = Application::awarded()
                                ->orderBy('awarded_date', 'DESC')
                                ->paginate(30)
                                ->appends('tab', 'reviewed');
        $this->checkPage($awarded);

        return view('training.skills.applications.index')->with([
            'unawarded' => $unawarded,
            'awarded'   => $awarded,
            'tab'       => request()->get('tab') == 'reviewed' ? 'reviewed' : 'pending',
        ]);
    }

    /**
     * View the details of a application.
     *
     * @param $id
     *
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        $application = Application::findOrFail($id);
        $this->authorize('view', $application);

        $levels = [
            0 => 'Do not award',
        ];
        for ($i = 1; $i <= 3; $i++) {
            if ($application->skill->isLevelAvailable($i)) {
                $levels[$i] = Skill::LEVEL_NAMES[$i];
            }
        }

        return view('training.skills.applications.view')->with([
            'application' => $application,
            'levels'      => $levels,
        ]);
    }

    /**
     * Process the application form and update the details.
     *
     * @param                          $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id, Request $request)
    {
        $application = Application::findOrFail($id);
        $this->authorize('update', $application);

        // If the application is already awarded, go back to viewing
        if ($application->isAwarded()) {
            return redirect()->route('training.skill.application.view', ['id' => $id]);
        }

        // Check the user isn't trying to review their own application
        $user = request()->user();
        if ($application->user_id == $user->id) {
            Notify::warning('You can\'t review your own application');
            return redirect()->route('training.skill.application.index');
        }

        // Validate the request
        $this->validateWith(validator($request->all(), [
            'awarded_level'   => 'required|in:0,1,2,3',
            'awarded_comment' => 'required_if:awarded_level,0',
        ], [
            'awarded_level.required'      => 'Please select the level to award',
            'awarded_level.in'            => 'Please select the level to award',
            'awarded_comment.required_if' => 'Please provide a reason why you haven\'t awarded the requested level',
        ])->after(function (Validator $validator) use ($application, $request) {
            $skill = Skill::find($application->skill_id);

            // Check the level awarded is available
            if ($request->get('awarded_level') > 0 && !$skill->isLevelAvailable($request->get('awarded_level'))) {
                $validator->errors()->add('awarded_level', 'Please select a level that\'s available');
            }
        }));

        // Update the application
        $attributes = [
            'awarded_level'   => $request->get('awarded_level'),
            'awarded_by'      => $user->id,
            'awarded_comment' => clean($request->get('awarded_comment')),
            'awarded_date'    => Carbon::now(),
        ];
        $application->update($attributes);
        Logger::log('training-skill-application.process', true, ['id' => $application->id] + $attributes);

        // Update the user's skill level
        if ($request->get('awarded_level') > 0) {
            $application->user->setSkillLevel($application->skill_id, $request->get('awarded_level'));
        }

        // Email the user
        Mail::to($application->user->email, $application->user->name)
            ->queue(new ApplicationProcessed($application));

        Notify::success('Application processed');
        return redirect()->route('training.skill.application.index');
    }

    /**
     * This method determines the skill levels that can be selected when submitting a application.
     *
     * @param \App\Models\Training\Skills\Skill|null $skill
     * @param \App\Models\Users\User                 $user
     *
     * @return array
     */
    private function determineSelectableApplicationSkillLevels(Skill $skill = null, User $user)
    {
        $levels = [];
        for ($i = 1; $i <= 3; $i++) {
            $levels[$i] = $skill == null ? true : $skill->isLevelAvailable($i);
        }

        if ($skill && $user->hasSkill($skill)) {
            for ($i = 1; $i <= $user->getSkillLevel($skill); $i++) {
                $levels[$i] = false;
            }
        }

        return $levels;
    }
}
