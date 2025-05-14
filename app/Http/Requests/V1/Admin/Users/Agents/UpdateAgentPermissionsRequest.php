<?php

namespace App\Http\Requests\V1\Admin\Users\Agents;

use App\Enums\Acl\PermissionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgentPermissionsRequest extends FormRequest
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
            'permissions'   => ['required','array'],
            'permissions.*' => [Rule::enum(PermissionType::class)],
        ];
    }
}
