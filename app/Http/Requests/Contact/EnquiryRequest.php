<?php

namespace App\Http\Requests\Contact;

use App\Http\Requests\Request;

class EnquiryRequest extends Request
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
            'name'                 => 'required|regex:/[a-zA-z]+\s[a-zA-z]+/',
            'email'                => 'required|email',
            'phone'                => 'nullable|phone',
            'message'              => 'required|string',
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
            'name.required'                 => 'Please enter your name',
            'name.regex'                    => 'Please enter both your forename and surname, using letters only',
            'email.required'                => 'Please enter your email address',
            'email.email'                   => 'Please enter a valid email address',
            'phone.phone'                   => 'Please enter a valid full phone number',
            'message.required'              => 'Please enter your enquiry',
            'message.string'                => 'Please enter your enquiry',
            'g-recaptcha-response.required' => 'Please fill in the CAPTCHA',
        ];
    }
}
