<?php

namespace App\Http\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class RepairRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->isMember();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'location' => 'required',
            'label' => 'required',
            'description' => 'required',
            'images' => 'array|max:5',
            'images.*' => 'image|max:20480|mimes:jpeg,png',
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
            'name.required' => 'Please enter the name of the broken equipment',
            'location.required' => 'Please enter the current location of the equipment',
            'label.required' => 'Please enter how the item is labelled',
            'description.required' => 'Please enter the details of the breakage',
            'images.array' => 'The images must be an array',
            'images.max' => 'Please upload no more than 5 images per report',
            'images.*.image' =>
                'One of the uploaded files could not be processed as an image. Ensure the file is not corrupt',
            'images.*.mimes' => 'Only JPEG and PNG images are supported',
            'images.*.max' => 'Each image must be less than 20MB',
        ];
    }
}
