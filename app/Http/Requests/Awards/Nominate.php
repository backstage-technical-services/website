<?php

namespace App\Http\Requests\Awards;

use App\Http\Requests\Request;
use App\Models\Awards\Season;

class Nominate extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $season = Season::find($this->route()->parameter('id'));

        return $season !== null && $this->user()->can('nominate', $season);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'award_id' => 'required|exists:awards,id',
            'nominee' => 'required',
            'reason' => 'required',
        ];
    }

    /**
     * Define the custom messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'award_id.required' => 'Please select the award',
            'award_id.exists' => 'Please select a valid award',
            'nominee.required' => 'Please enter the nominee',
            'reason.required' => 'Please enter the reason',
        ];
    }
}
