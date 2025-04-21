<?php

namespace App\Http\Requests\V1\Products\SocialMedia;

use App\Enums\Products\ProductType;
use App\Enums\Products\SocialMediaType;
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
            'title' => ['nullable', 'string'],
            'summary' => ['nullable', 'string', 'max:600'],
            'about_business' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric'],
            'type' => ['nullable', 'string'],
            'sub_type' => ['nullable', 'string'],
            'industry' => ['nullable', 'string'],
            'sub_industry' => ['nullable', 'string'],
            'allow_buyer_message' => ['boolean'],
            'is_private' => ['boolean'],
            'is_verified' => ['boolean'],
            'is_sold' => ['boolean'],
            'is_completed' => ['boolean'],
            'is_sponsored' => ['boolean'],
            'is_active' => ['boolean'],

            // YouTube-specific
            'url' => ['nullable', 'url'],
            'business_locations' => ['nullable', 'array'],
            'channel_age' => ['nullable', 'numeric'],
            'subscribers' => ['nullable', 'integer'],
            'monthly_revenue' => ['nullable', 'numeric'],
            'monthly_views' => ['nullable', 'numeric'],
            'monetization_method' => ['nullable', 'string'],
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
            'title' => $this->get('url'),
            'type' => ProductType::SOCIAL_MEDIA_ACCOUNT->value,
            'sub_type' => SocialMediaType::YOUTUBE->value,
        ]);
    }
}
