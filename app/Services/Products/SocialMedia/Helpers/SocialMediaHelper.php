<?php

namespace App\Services\Products\SocialMedia\Helpers;

class SocialMediaHelper
{
    public static function extractTitleFromUrl(string $url, string $platform): ?string
    {
        return match ($platform) {
            'youtube' => self::extractYouTubeTitle($url),
            'instagram' => self::extractInstagramTitle($url),
            'tiktok' => self::extractTikTokTitle($url),
            default => null,
        };
    }

    private static function extractYouTubeTitle(string $url): ?string
    {
        $url = self::normalizeUrl($url);

        $patterns = [
            '/youtube\.com\/channel\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/c\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/@([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/user\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private static function extractInstagramTitle(string $url): ?string
    {
        $url = self::normalizeUrl($url);

        if (preg_match('/instagram\.com\/([a-zA-Z0-9_.]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private static function extractTikTokTitle(string $url): ?string
    {
        $url = self::normalizeUrl($url);

        if (preg_match('/tiktok\.com\/@([a-zA-Z0-9_.]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private static function normalizeUrl(string $url): string
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        $url = rtrim($url, '/');
        return preg_replace('/\?.*$/', '', $url);
    }
}
