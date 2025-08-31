<?php

namespace App\Http\Controllers\Training\Skills;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\Skills\SkillRequest;
use App\Models\Training\Skills\AwardedSkill;
use App\Models\Training\Skills\Skill;
use Package\Notifications\Facades\Notify;

class SkillController extends Controller
{
    /**
     * SkillController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * View the skills index.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('index', Skill::class);
        return view('training.skills.index');
    }

    /**
     * View the form to create a new skill.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', Skill::class);
        return view('training.skills.create')->with([
            'skill' => new Skill([
                'level1' => '',
                'level2' => '',
                'level3' => '',
            ]),
        ]);
    }

    /**
     * Create a new skill.
     *
     * @param \App\Http\Requests\Training\Skills\SkillRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(SkillRequest $request)
    {
        $skill = Skill::create([
            'name' => clean($request->get('name')),
            'category_id' => clean($request->get('category_id')) ?: null,
            'description' => clean($request->get('description')),
            'level1' => $request->has('available.level1') ? clean($request->get('level1')) : null,
            'level2' => $request->has('available.level2') ? clean($request->get('level2')) : null,
            'level3' => $request->has('available.level3') ? clean($request->get('level3')) : null,
        ]);
        Notify::success('Skill created');
        return redirect()->route('training.skill.view', ['id' => $skill->id]);
    }

    /**
     * View the details of a skill.
     *
     * @param $id
     *
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        $skill = Skill::findOrFail($id);
        $this->authorize('view', $skill);
        return view('training.skills.view')->with([
            'skill' => $skill,
        ]);
    }

    /**
     * View the form to edit a skill.
     *
     * @param $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $skill = Skill::findOrFail($id);
        $this->authorize('edit', $skill);
        return view('training.skills.edit')->with([
            'skill' => $skill,
        ]);
    }

    /**
     * Update the details of a skill.
     *
     * @param                                                 $id
     * @param \App\Http\Requests\Training\Skills\SkillRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id, SkillRequest $request)
    {
        $skill = Skill::findOrFail($id);
        $skill->update([
            'name' => clean($request->get('name')),
            'category_id' => clean($request->get('category_id')) ?: null,
            'description' => clean($request->get('description')),
            'level1' => $request->has('available.level1') ? clean($request->get('level1')) : null,
            'level2' => $request->has('available.level2') ? clean($request->get('level2')) : null,
            'level3' => $request->has('available.level3') ? clean($request->get('level3')) : null,
        ]);
        Notify::success('Skill updated');
        return redirect()->route('training.skill.view', ['id' => $id]);
    }

    /**
     * Delete a skill.
     *
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $this->requireAjax();
        $skill = Skill::findOrFail($id);
        $this->authorize('delete', $skill);

        $skill->delete();
        Notify::success('Skill deleted');
        return $this->ajaxResponse(true);
    }

    /**
     * View the skills log.
     *
     * @return \Illuminate\View\View
     */
    public function log()
    {
        $this->authorize('log', Skill::class);
        $skills = AwardedSkill::orderBy('updated_at', 'DESC')->paginate(20);
        $this->checkPage($skills);

        return view('training.skills.log')->with([
            'skills' => $skills,
        ]);
    }
}
