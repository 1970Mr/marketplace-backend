<?php

namespace App\Http\Requests\V1\Products\SocialMedia;

use App\Http\Requests\V1\Products\SocialMedia\Abstracts\BaseSocialMediaRequest;

class SocialAccountRequest extends BaseSocialMediaRequest
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
        return array_merge(parent::rules(), [
            'followers_count' => ['nullable', 'integer'],
            'posts_count' => ['nullable', 'integer'],
            'average_likes' => ['nullable', 'numeric'],
        ]);
    }
}
