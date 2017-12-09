<?php

namespace App\Http\Requests\Training\Skills;

use App\Models\Training\Skills\Skill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AwardSkill extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->canAwardSkill(Skill::find($this->get('skill_id')));
    }

    /**
     * Override the validator instance to perform additional checks.
     *
     * @return Validator
     */
    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        return $validator->after(function (Validator $validator) {
            $skill = Skill::find($this->get('skill_id'));
            if ((int)$this->get('level') > 0 && !$skill->isLevelAvailable($this->get('level'))) {
                $validator->errors()->add('level', 'That level isn\'t available for the selected skill');
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
            'skill_id' => ['required', 'exists:training_skills,id'],
            'level'    => ['required', 'in:' . implode(',', array_keys(Skill::LEVEL_NAMES))],
            'members'  => ['required', 'array', 'exists:users,id'],
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
            'skill_id.required' => 'Please select the skill',
            'skill_id.exists'   => 'Please select a valid skill',
            'level.required'    => 'Please select a level',
            'level.in'          => 'Please select a valid level',
            'members.required'  => 'Please select at least 1 member',
            'members.array'     => 'Please provide a valid selection of members',
            'members.exists'    => 'Please ensure all of the members are valid',
        ];
    }
}
