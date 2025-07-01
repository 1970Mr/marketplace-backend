<?php

namespace App\Http\Requests\V1\Products\SocialMedia\Abstracts;

use App\Enums\Products\ProductType;
use App\Services\Products\SocialMedia\Helpers\SocialMediaHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

abstract class BaseSocialMediaRequest extends FormRequest
{
    protected string $mediaType;

    public function rules(): array
    {
        return [
            // Product fields
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

            // Common fields
            'url' => ['nullable', 'url'],
            'business_locations' => ['nullable', 'array'],
            'business_age' => ['nullable', 'integer'],
            'analytics_screenshot' => ['nullable', 'image', 'max:2048'],
            'listing_images' => ['nullable', 'array', 'max:3'],
            'listing_images.*' => ['image', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $url = $this->get('url');
        $extractedTitle = $url ? SocialMediaHelper::extractTitleFromUrl($url, strtolower($this->mediaType)) : null;

        $this->merge([
            'uuid' => $this->get('uuid') ?? Str::uuid(),
            'user_id' => Auth::id(),
            'title' => $extractedTitle ?? $url,
            'type' => ProductType::SOCIAL_MEDIA_ACCOUNT->value,
            'sub_type' => $this->mediaType,
        ]);
    }
}
