<?php

namespace App\Http\Requests\V1\Escrow;

use App\Enums\Escrow\Weekday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProposeSlotsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'weekdays'   => ['required','array'],
            'weekdays.*' => ['required', Rule::enum(Weekday::class)],
            'times'      => ['required','array'],
            'times.*'    => ['required','date_format:H:i'],
        ];
    }
}
