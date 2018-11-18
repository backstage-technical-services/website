<?php

namespace App\Http\Controllers\Training\Skills;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\Skills\AwardSkill;
use App\Logger;
use App\Models\Training\Skills\AwardedSkill;
use App\Models\Training\Skills\Proposal;
use App\Models\Training\Skills\Skill;
use App\Models\Users\User;
use bnjns\LaravelNotifications\Facades\Notify;
use Carbon\Carbon;

class AwardedController extends Controller
{
    /**
     * AwardController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * View the form to award a skill.
     *
     * @param null|int $id
     *
     * @return $this
     */
    public function awardForm($id = null)
    {
        $this->authorize('award', $id ? Skill::find($id) : Skill::class);
        return view('training.skills.award')->with([
            'skill' => $id === null ? null : Skill::find($id),
        ]);
    }

    /**
     * Award a skill.
     *
     * @param \App\Http\Requests\Training\Skills\AwardSkill $request
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function award(AwardSkill $request)
    {
        $skill   = Skill::findOrFail($request->get('skill_id'));
        $date    = Carbon::now();
        $members = (array)$request->get('members');
        $level   = (int)$request->get('level');

        // Check for the current member
        if (($key = array_search($request->user()->id, $members)) !== false) {
            Notify::warning('You can\'t award yourself a skill level');
            unset($members[$key]);
        }

        // If there are no members to update, just go back to the previous page
        if (count($members) == 0) {
            return redirect()->back()
                             ->withInput($request->all());
        }

        // Award any outstanding applications
        Proposal::notAwarded()
                ->where('skill_id', $skill->id)
                ->whereIn('user_id', [$members])
                ->where('proposed_level', '<=', $level)
                ->update([
                    'awarded_level'   => $level,
                    'awarded_by'      => $request->user()->id,
                    'awarded_comment' => 'Awarded using \'Award Skill\' functionality',
                    'awarded_date'    => $date,
                ]);

        // Update each user if they don't have a higher level already
        foreach ($members as $member) {
            $user = User::find($member);
            if ((int)$user->getSkillLevel($skill) < $level) {
                $user->setSkillLevel($skill->id, $level);
                Logger::log('training-skill.award', true, ['user_id' => $user->id, 'skill_id' => $skill->id, 'level' => $level]);
            }
        }

        Notify::success('Skill awarded to selected members');
        return redirect()->route('training.skill.index');
    }

    /**
     * View the form to revoke a skill.
     *
     * @param null|int $id
     *
     * @return $this
     */
    public function revokeForm($id = null)
    {
        $this->authorize('revoke', $id ? Skill::find($id) : Skill::class);
        return view('training.skills.revoke')->with([
            'skill'  => $id === null ? null : Skill::find($id),
            'levels' => [
                0 => 'Completely revoke',
                1 => Skill::LEVEL_NAMES[1],
                2 => Skill::LEVEL_NAMES[2],
            ],
        ]);
    }

    /**
     * Revoke or reduce a skill level.
     *
     * @param \App\Http\Requests\Training\Skills\AwardSkill $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revoke(AwardSkill $request)
    {
        $skill   = Skill::findOrFail($request->get('skill_id'));
        $members = (array)$request->get('members');
        $level   = (int)$request->get('level');

        // Check for the current member
        if (($key = array_search($request->user()->id, $members)) !== false) {
            Notify::warning('You can\'t revoke one of your own skills');
            unset($members[$key]);
        }

        // If there are no members to update, just go back to the previous page
        if (count($members) == 0) {
            return redirect()->back()
                             ->withInput($request->all());
        }

        // Reduce or revoke each member
        $skills = AwardedSkill::where('skill_id', $skill->id)
                              ->whereIn('user_id', $members)
                              ->where('level', '>', $level);
        if ($level == 0) {
            $skills->delete();
            Notify::success('Skill revoked');
        } else {
            $skills->update([
                'level'      => $level,
                'awarded_by' => $request->user()->id,
            ]);
            Notify::success('Skill level reduced');
        }
        array_walk($members, function ($memberId) use ($skill, $level) {
            Logger::log('training-skill.revoke', true, ['user_id' => $memberId, 'skill_id' => $skill->id, 'level' => $level]);
        });

        return redirect()->route('training.skill.index');
    }
}
