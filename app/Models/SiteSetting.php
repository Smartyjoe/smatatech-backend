<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasUuid;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("site_setting_{$key}", 3600, function () use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget("site_setting_{$key}");
        Cache::forget('site_settings_all');

        return $setting;
    }

    /**
     * Get all settings as array.
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('site_settings_all', 3600, function () {
            $settings = [];
            foreach (self::all() as $setting) {
                $settings[$setting->key] = self::castValue($setting->value, $setting->type);
            }
            return $settings;
        });
    }

    /**
     * Get public settings for frontend.
     */
    public static function getPublicSettings(): array
    {
        $settings = self::getAllSettings();
        $socialLinks = $settings['social_links'] ?? [];
        
        return [
            'siteName' => $settings['site_name'] ?? 'Smatatech Technologies',
            'siteTagline' => $settings['site_tagline'] ?? 'AI-Powered Digital Solutions',
            'siteDescription' => $settings['site_description'] ?? '',
            'logo' => [
                'light' => $settings['logo_light'] ?? null,
                'dark' => $settings['logo_dark'] ?? null,
                'favicon' => $settings['favicon'] ?? '/favicon.ico',
            ],
            'contact' => [
                'email' => $settings['contact_email'] ?? '',
                'phone' => $settings['contact_phone'] ?? '',
                'whatsapp' => $settings['contact_whatsapp'] ?? null,
                'address' => $settings['address'] ?? '',
                'city' => $settings['city'] ?? '',
                'country' => $settings['country'] ?? '',
            ],
            'contactEmail' => $settings['contact_email'] ?? '',
            'contactPhone' => $settings['contact_phone'] ?? '',
            'address' => $settings['address'] ?? '',
            'socialLinks' => [
                'facebook' => $socialLinks['facebook'] ?? null,
                'twitter' => $socialLinks['twitter'] ?? null,
                'linkedin' => $socialLinks['linkedin'] ?? null,
                'instagram' => $socialLinks['instagram'] ?? null,
                'youtube' => $socialLinks['youtube'] ?? null,
                'github' => $socialLinks['github'] ?? null,
                'whatsapp' => $socialLinks['whatsapp'] ?? null,
            ],
            'seo' => [
                'defaultTitle' => $settings['seo_title'] ?? $settings['site_name'] ?? 'Smatatech',
                'titleSeparator' => $settings['seo_title_separator'] ?? ' | ',
                'defaultDescription' => $settings['seo_description'] ?? $settings['site_description'] ?? '',
                'defaultKeywords' => $settings['seo_keywords'] ?? [],
                'ogImage' => $settings['og_image'] ?? null,
            ],
            'footer' => [
                'copyrightText' => $settings['footer_copyright'] ?? '© ' . date('Y') . ' Smatatech Technologies. All rights reserved.',
                'showSocialLinks' => $settings['footer_show_social'] ?? true,
            ],
            'heroStats' => $settings['hero_stats'] ?? [
                ['value' => '150+', 'label' => 'Projects Delivered'],
                ['value' => '50+', 'label' => 'Happy Clients'],
                ['value' => '5+', 'label' => 'Years Experience'],
            ],
            'features' => [
                'chatbotEnabled' => $settings['chatbot_enabled'] ?? false,
                'blogEnabled' => $settings['blog_enabled'] ?? true,
                'newsletterEnabled' => $settings['newsletter_enabled'] ?? true,
            ],
        ];
    }

    /**
     * Cast value based on type.
     */
    protected static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
