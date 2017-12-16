<?php

namespace App\Http\Requests\Events;

use App\Models\Events\Email;
use Illuminate\Foundation\Http\FormRequest;

class EmailRequest extends FormRequest
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
        return Email::getValidationRules(['header', 'body', 'crew']);
    }

    /**
     * Get the validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return Email::getValidationMessages(['header', 'body', 'crew']);
    }
}