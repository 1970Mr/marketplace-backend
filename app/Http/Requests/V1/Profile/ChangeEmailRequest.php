<?php

namespace App\Http\Requests\V1\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ChangeEmailRequest extends FormRequest
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
            'email' => ['required', 'email', 'unique:users,email,' . auth()->id()],
            'password' => ['required', 'string'],
        ];
    }
}
