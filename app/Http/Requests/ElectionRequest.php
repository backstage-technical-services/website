<?php

namespace App\Http\Requests;

use App\Models\Elections\Election;

class ElectionRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->route == 'election.store') {
            return $this->user()->can('create', Election::class);
        } else if ($this->route == 'election.update') {
            $election = Election::find($this->route('id'));

            return $this->user()->can('update', $election);
        } else {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type'                      => 'required|in:' . implode(',', array_keys(Election::$Types)),
            'bathstudent_id'            => 'nullable|integer',
            'userTZ_hustings_time'      => 'required|date_format:Y-m-d H:i|before:userTZ_voting_end',
            'hustings_location'         => 'required',
            'userTZ_nominations_start'  => 'required|date_format:Y-m-d H:i',
            'userTZ_nominations_end'    => 'required|date_format:Y-m-d H:i|after:userTZ_nominations_start',
            'userTZ_voting_start'       => 'required|date_format:Y-m-d H:i|after:userTZ_nominations_end',
            'userTZ_voting_end'         => 'required|date_format:Y-m-d H:i|after:userTZ_voting_start',
            'positions_checked'         => 'required_if:type,2|array',
            'positions'                 => 'required_if:type,2|array',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'type.required'                     => 'Please select an election type.',
            'type.in'                           => 'Please select a valid election type.',
            'bathstudent_id.integer'            => 'Please enter an integer',
            'userTZ_hustings_time.required'     => 'Please enter the date of the hustings',
            'userTZ_hustings_time.datetime'     => 'Please enter a valid date',
            'userTZ_hustings_time.before'       => 'Hustings must happen before voting closes!',
            'hustings_location.required'        => 'Please enter the hustings location',
            'userTZ_nominations_start.required' => 'Please enter when the nominations open',
            'userTZ_nominations_start.datetime' => 'Please enter a valid date for when the nominations open',
            'userTZ_nominations_end.required'   => 'Please enter when the nominations close',
            'userTZ_nominations_end.datetime'   => 'Please enter a valid date for when the nominations close',
            'userTZ_nominations_end.after'      => 'Nominations must open before they close!',
            'userTZ_voting_start.required'      => 'Please enter when voting opens',
            'userTZ_voting_start.after'         => 'Nominations must close before voting starts!',
            'userTZ_voting_start.datetime'      => 'Please enter a valid date for when voting opens',
            'userTZ_voting_end.required'        => 'Please enter when voting closes',
            'userTZ_voting_end.datetime'        => 'Please enter a valid date for when voting closes',
            'userTZ_voting_end.after'           => 'Voting must open before it closes!',
            'positions_checked.required_if'     => 'Please select at least 1 position',
            'positions_checked.array'           => 'Please select at least 1 position',
        ];
    }
}
