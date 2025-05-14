<?php

namespace App\Http\Requests\V1\Admin\Users\Agents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgentRequest extends FormRequest
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
        $userId = $this->route('user')->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes', 'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            'avatar' => ['sometimes', 'file', 'image', 'max:2048'],
        ];
    }
}
