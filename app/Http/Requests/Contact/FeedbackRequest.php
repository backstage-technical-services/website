<?php

namespace App\Http\Requests\Contact;

use App\Http\Requests\Request;

class FeedbackRequest extends Request
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
            'event'                => 'required|string',
            'feedback'             => 'required|string',
            'g-recaptcha-response' => 'required|captcha',
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
            'event.required'                => 'Please enter the event name',
            'event.string'                  => 'Please enter the event name',
            'feedback.required'             => 'Please enter your feedback',
            'feedback.string'               => 'Please enter your feedback',
            'g-recaptcha-response.required' => 'Please fill in the CAPTCHA',
        ];
    }
}
