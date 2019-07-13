<?php

namespace App\Http\Requests\Events;

use App\Models\Events\Event;
use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    /**
     * Define the fields to get the validation rules and messages for.
     *
     * @var array
     */
    private $fields = [
        'name',
        'em_id',
        'type',
        'description',
        'venue',
        'venue_type',
        'client_type',
        'date_start',
        'date_end',
        'time_start',
        'time_end',
        'production_charge',
    ];

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
        $fields = $this->fields;

        if ($this->has('one_day')) {
            unset($fields[array_search('date_end', $fields)]);
        }
        if ($this->get('type') != Event::TYPE_EVENT) {
            unset($fields[array_search('venue_type', $fields)]);
            unset($fields[array_search('client_type', $fields)]);
        }

        $fields = array_values($fields);
        return Event::getValidationRules($fields);
    }

    /**
     * Get the validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return Event::getValidationMessages($this->fields);
    }
}
