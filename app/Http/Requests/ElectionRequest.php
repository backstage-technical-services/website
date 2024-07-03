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
            'type'              => 'required|in:' . implode(',', array_keys(Election::$Types)),
            'bathstudent_id'    => 'nullable|integer',
            'hustings_time'     => 'required|datetime',
            'hustings_location' => 'required',
            'nominations_start' => 'required|datetime',
            'nominations_end'   => 'required|datetime|after:nominations_start',
            'voting_start'      => 'required|datetime',
            'voting_end'        => 'required|datetime|after:voting_start',
            'positions_checked' => 'required_if:type,2|array',
            'positions'         => 'required_if:type,2|array',
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
            'type.required'                 => 'Please select an election type.',
            'type.in'                       => 'Please select a valid election type.',
            'bathstudent_id.integer'        => 'Please enter an integer',
            'hustings_time.required'        => 'Please enter the date of the hustings',
            'hustings_time.datetime'        => 'Please enter a valid date',
            'hustings_location.required'    => 'Please enter the hustings location',
            'nominations_start.required'    => 'Please enter when the nominations open',
            'nominations_end.required'      => 'Please enter when the nominations close',
            'nominations_start.datetime'    => 'Please enter a valid date for when the nominations open',
            'nominations_end.datetime'      => 'Please enter a valid date for when the nominations close',
            'nominations_end.after'         => 'The nominations have to close after they\'ve started!',
            'voting_start.required'         => 'Please enter when voting opens',
            'voting_end.required'           => 'Please enter when voting closes',
            'voting_start.datetime'         => 'Please enter a valid date for when voting opens',
            'voting_end.datetime'           => 'Please enter a valid date for when voting closes',
            'voting_end.after'              => 'Voting has to close after it\'s opened!',
            'positions_checked.required_if' => 'Please select at least 1 position',
            'positions_checked.array'       => 'Please select at least 1 position',
        ];
    }
}
