<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends BaseApiController
{
    /**
     * Get site settings.
     * GET /admin/settings
     */
    public function index(): JsonResponse
    {
        $settings = SiteSetting::getAllSettings();

        return $this->successResponse([
            'siteName' => $settings['site_name'] ?? 'Smatatech Technologies',
            'siteDescription' => $settings['site_description'] ?? '',
            'contactEmail' => $settings['contact_email'] ?? '',
            'contactPhone' => $settings['contact_phone'] ?? '',
            'address' => $settings['address'] ?? '',
            'socialLinks' => $settings['social_links'] ?? [
                'facebook' => '',
                'twitter' => '',
                'linkedin' => '',
                'instagram' => '',
            ],
        ]);
    }

    /**
     * Update site settings.
     * PUT /admin/settings
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'siteName' => 'sometimes|string|max:255',
            'siteDescription' => 'nullable|string',
            'contactEmail' => 'nullable|email|max:255',
            'contactPhone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'socialLinks' => 'nullable|array',
            'socialLinks.facebook' => 'nullable|url|max:255',
            'socialLinks.twitter' => 'nullable|url|max:255',
            'socialLinks.linkedin' => 'nullable|url|max:255',
            'socialLinks.instagram' => 'nullable|url|max:255',
        ]);

        if (isset($validated['siteName'])) {
            SiteSetting::set('site_name', $validated['siteName'], 'string', 'general');
        }
        if (array_key_exists('siteDescription', $validated)) {
            SiteSetting::set('site_description', $validated['siteDescription'], 'string', 'general');
        }
        if (array_key_exists('contactEmail', $validated)) {
            SiteSetting::set('contact_email', $validated['contactEmail'], 'string', 'contact');
        }
        if (array_key_exists('contactPhone', $validated)) {
            SiteSetting::set('contact_phone', $validated['contactPhone'], 'string', 'contact');
        }
        if (array_key_exists('address', $validated)) {
            SiteSetting::set('address', $validated['address'], 'string', 'contact');
        }
        if (isset($validated['socialLinks'])) {
            SiteSetting::set('social_links', $validated['socialLinks'], 'json', 'social');
        }

        // Clear all settings cache
        Cache::forget('site_settings_all');

        ActivityLog::log(
            'site_settings_updated',
            'Site settings updated',
            'Site configuration was updated',
            $request->user()
        );

        return $this->successResponse($this->getFormattedSettings(), 'Site settings updated.');
    }

    /**
     * Get formatted settings array.
     */
    private function getFormattedSettings(): array
    {
        $settings = SiteSetting::getAllSettings();

        return [
            'siteName' => $settings['site_name'] ?? 'Smatatech Technologies',
            'siteDescription' => $settings['site_description'] ?? '',
            'contactEmail' => $settings['contact_email'] ?? '',
            'contactPhone' => $settings['contact_phone'] ?? '',
            'address' => $settings['address'] ?? '',
            'socialLinks' => $settings['social_links'] ?? [
                'facebook' => '',
                'twitter' => '',
                'linkedin' => '',
                'instagram' => '',
            ],
        ];
    }
}
