<?php

namespace App\Http\Requests;

use App\Models\Quote;
use Carbon\Carbon;

class QuoteRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', Quote::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'culprit' => 'required',
            'date'    => 'required|datetime|before:' . Carbon::now()->tzUser(),
            'quote'   => 'required',
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
            'culprit.required' => 'Please enter the culprit',
            'date.required'    => 'Please specify when it was said',
            'date.datetime'    => 'Please enter a valid date',
            'date.before'      => 'Try not to predict the future!',
            'quote.required'   => 'Please enter what was said',
        ];
    }
}
