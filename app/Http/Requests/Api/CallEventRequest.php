<?php

namespace App\Http\Requests\Api;

use App\Enums\CallEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CallEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }


    public function rules(): array
    {
        $rules= [
         'call_id' => 'required|unique:call_events,call_id',
         'from' => 'required',
         'to' => 'required',
         'event_type' => ['required',new Enum(CallEventType::class)],
         'timestamp' => ['required', 'date', 'date_format:Y-m-d H:i'],
        ];

        if ($this->input('event_type') == CallEventType::CALL_ENDED->value) {
            $rules['duration'] = 'required|integer|min:0';
        }

        return $rules;
    }
}
