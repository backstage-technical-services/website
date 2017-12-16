<?php

namespace App\Http\Requests\Contact;

use App\Http\Requests\Request;
use Carbon\Carbon;

class AccidentRequest extends Request
{
    /**
     * Store a list of personnel types
     *
     * @var array
     */
    public static $PersonTypes = [
        'Academic',
        'Administrative',
        'Catering',
        'Cleaning',
        'Contractor',
        'Grounds/Gardening',
        'Maintenance',
        'Portering',
        'Postgraduate',
        'Security',
        'Technical',
        'Undergraduate',
        'other' => 'Other',
    ];

    /**
     * Store a list of accident severities
     *
     * @var array
     */
    public static $Severities = [
        'No absence',
        'Absent for first aid treatment only',
        'Absent for less than 3 days',
        'Absent for more than 4 days',
        'Absence not known',
    ];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Override the validate method to create a custom entry for the formatted date/time.
     */
    public function validate()
    {
        $this->merge([
            'date_formatted'    => Carbon::createFromFormat('Y-m-d H:i', $this->get('date') . ' ' . $this->get('time')),
            'person_type_email' => $this->get('person_type') == 'other' ? $this->get('person_type_other') : self::$PersonTypes[$this->get('person_type')],
            'severity_email'    => self::$Severities[$this->get('severity')],
        ]);

        return parent::validate();;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'location'          => 'required',
            'date'              => 'required|date_format:Y-m-d',
            'time'              => 'required|date_format:H:i',
            'severity'          => 'required|in:' . implode(',', array_keys(self::$Severities)),
            'absence_details'   => 'required_if:severity,1,2,3',
            'details'           => 'required',
            'injured_name'      => 'required',
            'contact_name'      => 'required',
            'contact_email'     => 'required|email',
            'contact_phone'     => 'required|phone',
            'person_type'       => 'required|in:' . implode(',', array_keys(self::$PersonTypes)),
            'person_type_other' => 'required_if:person_type,other',
        ];
    }

    /**
     * Set the custom messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'location.required'             => 'Please provide the accident location',
            'date.required'                 => 'Please provide the date of the accident',
            'date.date_format'              => 'Please provide the date in the form \'dd/mm/YYYY\'',
            'time.required'                 => 'Please provide the approximate time of the accident',
            'time.date_format'              => 'Please provide the time in the form \'hh:mm\'',
            'severity.required'             => 'Please select the accident severity',
            'severity.in'                   => 'Please select one of the provided severities',
            'absence_details.required_if'   => 'Please enter any details of their absence from employment or studies',
            'details.required'              => 'Please provide the accident details',
            'injured_name.required'         => 'Please provide the name of the injured party',
            'contact_name.required'         => 'Please provide the name of the contact person',
            'contact_email.required'        => 'Please provide an email address for the contact',
            'contact_email.email'           => 'Please provide a valid email address',
            'contact_phone.required'        => 'Please provide a phone number for the contact',
            'contact_phone.phone'           => 'Please provide a valid phone number',
            'person_type.required'          => 'Please select an employment category for the injured party',
            'person_type.in'                => 'Please select one of the provided employment categories',
            'person_type_other.required_if' => 'Please enter an alternative employment category',
        ];
    }
}
