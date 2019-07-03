<?php

namespace App\Http\Requests\Contact;

use App\Http\Requests\Request;

class NearMissRequest extends Request
{
    /**
     * Any one is able to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'location'               => ['required'],
            'date'                   => ['required', 'date_format:Y-m-d'],
            'time'                   => ['required', 'date_format:H:i'],
            'details'                => ['required'],
            'safety_recommendations' => ['nullable'],
            'user_name'              => ['nullable', 'required_with:user_email'],
            'user_email'             => ['nullable', 'email'],
        ];
    }

    /**
     * Define the custom messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'location.required'       => 'Please provide the location',
            'date.required'           => 'Please provide a rough date',
            'date.date_format'        => 'Please provide a valid date',
            'time.required'           => 'Please provide a rough time',
            'time.date_format'        => 'Please provide a valid time',
            'details.required'        => 'Please provide details of the Near Miss',
            'user_name.required_with' => 'If you are providing your email address, please also provide your name',
            'user_email.email'        => 'Please provide a valid email',
        ];
    }
}