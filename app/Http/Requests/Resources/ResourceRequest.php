<?php

namespace App\Http\Requests\Resources;

use App\Models\Resources\Resource;
use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    /**
     * Define the default rules for using files.
     *
     * @var string
     */
    const FILE_RULES = 'mimes:pdf|max:50000';

    /**
     * Define the default messages for using files.
     *
     * @var array
     */
    const FILE_MESSAGES = [
        'file.required' => 'Select a file to upload',
        'file.required_if' => 'Select a file to upload',
        'file.mimes' => 'Only PDFs are currently supported',
        'file.max' => 'Maximum file size is 50MB',
    ];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $route = $this->route()->getName();

        if ($route == 'resource.store') {
            return $this->user()->can('create', Resource::class);
        } elseif ($route == 'resource.update') {
            return $this->user()->can('update', Resource::find($this->route()->parameter('id')));
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
        $route = $this->route()->getName();

        // Base rules
        $rules = [
            'title' => 'required',
            'category_id' => 'nullable|exists:resource_categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:resource_tags,id',
            'event_id' => 'nullable|exists:events,id',
            'access' => 'nullable|in:' . implode(',', array_keys(Resource::ACCESS)),
        ];

        // Add extra rules for creating
        if ($route == 'resource.store') {
            $rules['type'] = 'required|in:' . implode(',', array_keys(Resource::TYPES));
            $rules['file'] = 'required_if:type,' . Resource::TYPE_FILE . '|' . self::FILE_RULES;
            $rules['drive_id'] = 'required_if:type,' . Resource::TYPE_GDOC;
        }

        return $rules;
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'Enter the resource\'s name',
            'type.required' => 'Select the resource type',
            'type.in' => 'Select a valid resource type',
            'drive_id.required_if' => 'Enter the document ID',
            'category_id.exists' => 'Select a valid category',
            'tags.array' => 'Select some tags',
            'tags.*.exists' => 'Select a valid tag',
            'event_id.exists' => 'Select a valid event',
            'access.in' => 'Select a valid access type',
        ] + self::FILE_MESSAGES;
    }
}
