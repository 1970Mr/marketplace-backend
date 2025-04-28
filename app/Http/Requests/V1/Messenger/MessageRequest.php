<?php

namespace App\Http\Requests\V1\Messenger;

use App\Enums\Messenger\MessageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MessageRequest extends FormRequest
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
            'chat_uuid' => 'required|exists:chats,uuid',
            'content' => 'required_without:offer_id|string|max:1000',
            'type' => ['sometimes', Rule::enum(MessageType::class)],
        ];
    }
}
