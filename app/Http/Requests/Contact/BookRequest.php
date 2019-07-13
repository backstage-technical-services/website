<?php

namespace App\Http\Requests\Contact;

use App\Http\Requests\Request;

class BookRequest extends Request
{
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'event_name'           => 'required',
            'event_venue'          => 'required',
            'event_dates'          => 'required',
            'event_access'         => 'required|array',
            'event_club'           => 'required',
            'contact_name'         => 'required|regex:/[a-zA-z]+\s[a-zA-z]+/',
            'contact_email'        => 'required|email',
            'contact_phone'        => 'nullable|phone',
            'terms'                => 'accepted',
            'g-recaptcha-response' => 'required|recaptcha',
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
            'event_name.required'           => 'Please enter the event name',
            'event_venue.required'          => 'Please enter the event venue',
            'event_dates.required'          => 'Please provide the event date(s)',
            'event_access.required'         => 'Please specify when we can access the venue',
            'event_access.array'            => 'Please select a combination of morning, afternoon and evening for the venue access time',
            'event_club.required'           => 'Please enter the name of the club/organisation',
            'contact_name.required'         => 'Please enter the contact\'s name',
            'contact_name.regex'            => 'Please enter both their forename and surname, using letters only',
            'contact_email.required'        => 'Please enter the contact\'s email address',
            'contact_email.email'           => 'Please enter a valid email address',
            'contact_phone.phone'           => 'Please enter a valid phone number',
            'g-recaptcha-response.required' => 'Please fill in the CAPTCHA',
        ];
    }
}
