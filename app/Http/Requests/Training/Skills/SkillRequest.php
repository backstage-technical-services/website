<?php

namespace App\Http\Requests\Training\Skills;

use App\Models\Training\Skills\Skill;
use Illuminate\Foundation\Http\FormRequest;

class SkillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        switch ($this->route()->getName()) {
            case 'training.skill.store':
                return $this->user()->can('create', Skill::class);
            case 'training.skill.update':
                $skill = $this->route()->parameter('id');
                return $this->user()->can('edit', Skill::find($skill));
            default:
                return false;
        }
    }

    /**
     * Override getting the validator instance to check the number of available skills.
     *
     * @return $this
     */
    protected function getValidatorInstance()
    {
        return parent::getValidatorInstance()->after(function ($validator) {
            if (count($this->get('available')) == 0) {
                $validator->errors()->add('levels', 'At least 1 level needs to be available.');
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
            'name' => ['required'],
            'category_id' => ['nullable', 'exists:training_categories,id'],
            'description' => ['required'],
            'level1' => ['required_with:available.level1'],
            'level2' => ['required_with:available.level2'],
            'level3' => ['required_with:available.level3'],
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
            'name.required' => 'Please enter the skill name',
            'category_id.exists' => 'Please choose a valid category',
            'description.required' => 'Please enter a description of the skill',
            'level1.required_with' => 'Please enter the requirements for ' . Skill::LEVEL_NAMES[1],
            'level2.required_with' => 'Please enter the requirements for ' . Skill::LEVEL_NAMES[2],
            'level3.required_with' => 'Please enter the requirements for ' . Skill::LEVEL_NAMES[3],
        ];
    }
}
