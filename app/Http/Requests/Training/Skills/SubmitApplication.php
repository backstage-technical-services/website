<?php

namespace App\Http\Requests\Training\Skills;

use App\Models\Training\Skills\Application;
use App\Models\Training\Skills\Skill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitApplication extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('apply', Application::class);
    }

    /**
     * Override the validator instance to add additional validation checks.
     *
     * @return Validator
     */
    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        return $validator->after(function (Validator $validator) {
            $skill = Skill::find($this->get('skill_id'));
            $user  = $this->user();

            // Check the user doesn't already have a higher level
            if ((int)$user->getSkillLevel($skill) >= $this->get('level')) {
                $validator->errors()->add('level', 'Please choose a level you don\'t already have');
            }
            // Check the user doesn't already have a application pending
            if (Application::where('skill_id', $skill->id)->where('user_id', $user->id)->notAwarded()->count() > 0) {
                $validator->errors()->add('skill_id', 'You already have a application pending for this skill');
            }
            // Check the level requested is available
            if (!$skill->isLevelAvailable($this->get('level'))) {
                $validator->errors()->add('level', 'Please select a level that\'s available');
            }
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'skill_id'  => ['required', 'exists:training_skills,id'],
            'level'     => ['required', 'in:' . implode(',', array_keys(Skill::LEVEL_NAMES))],
            'reasoning' => ['required'],
        ];
    }

    /**
     * Get the validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'skill_id.required'  => 'Please select the skill you\'re applying for',
            'skill_id.exists'    => 'Please select a valid skill',
            'level.required'     => 'Please select a level you\'re applying for',
            'level.in'           => 'Please select a valid level',
            'reasoning.required' => 'Please provide some reasoning for your application',
        ];
    }
}
