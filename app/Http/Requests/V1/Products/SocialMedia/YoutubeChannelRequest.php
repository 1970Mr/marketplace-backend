<?php

namespace App\Http\Requests\V1\Products\SocialMedial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class YoutubeChannelRequest extends FormRequest
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
            'uuid' => ['required'],
            'user_id' => ['nullable'],
            'url' => ['nullable', 'url'],
            'category' => ['nullable', 'string'],
            'sub_category' => ['nullable', 'string'],
            'business_location' => ['nullable', 'array'],
            'age_of_channel' => ['nullable', 'numeric'],
            'subscribers' => ['nullable', 'integer'],
            'monthly_revenue' => ['nullable', 'numeric'],
            'monthly_views' => ['nullable', 'numeric'],
            'monetization_method' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric'],
            'summary' => ['nullable', 'string'],
            'about_channel' => ['nullable', 'string'],
            'allow_buyer_messages' => ['boolean'],
            'is_private' => ['boolean'],
            'analytics_screenshot' => ['nullable', 'image', 'max:2048'],
            'listing_images' => ['nullable', 'array', 'max:3'],
            'listing_images.*' => ['image', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'uuid' => $this->get('uuid') ?? Str::uuid(),
            'user_id' => auth()->id(),
        ]);
    }
}
