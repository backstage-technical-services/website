<?php

namespace App\Http\Requests;

class CommitteeRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'        => 'required',
            'email'       => 'required|email',
            'description' => 'required',
            'user_id'     => 'required|exists:users,id',
            'order'       => 'required|min:0',
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
            'name.required'        => 'Please enter the role\'s name',
            'email.required'       => 'Please enter the role\'s email address',
            'email.email'          => 'Please enter a valid email address',
            'description.required' => 'Please enter a role description',
            'user_id.required'     => 'Please choose the user who holds this role',
            'user_id.exists'       => 'Please choose a valid user',
            'order.required'       => 'Please select where this role should appear',
            'order.min'            => 'Please select a valid position',
        ];
    }
}
