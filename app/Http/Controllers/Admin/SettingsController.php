<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $formattedSettings = [
            'siteName' => SiteSetting::get('site_name', ''),
            'siteDescription' => SiteSetting::get('site_description', ''),
            'contactEmail' => SiteSetting::get('contact_email', ''),
            'contactPhone' => SiteSetting::get('contact_phone', ''),
            'address' => SiteSetting::get('address', ''),
            'socialLinks' => [
                'facebook' => SiteSetting::get('social_facebook', ''),
                'twitter' => SiteSetting::get('social_twitter', ''),
                'linkedin' => SiteSetting::get('social_linkedin', ''),
                'instagram' => SiteSetting::get('social_instagram', ''),
                'youtube' => SiteSetting::get('social_youtube', ''),
            ],
            'logo' => SiteSetting::get('logo', ''),
            'favicon' => SiteSetting::get('favicon', ''),
            'footerText' => SiteSetting::get('footer_text', ''),
        ];

        return $this->successResponse($formattedSettings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'siteName' => 'nullable|string',
            'siteDescription' => 'nullable|string',
            'contactEmail' => 'nullable|email',
            'contactPhone' => 'nullable|string',
            'address' => 'nullable|string',
            'socialLinks' => 'nullable|array',
            'socialLinks.facebook' => 'nullable|string',
            'socialLinks.twitter' => 'nullable|string',
            'socialLinks.linkedin' => 'nullable|string',
            'socialLinks.instagram' => 'nullable|string',
            'socialLinks.youtube' => 'nullable|string',
            'logo' => 'nullable|string',
            'favicon' => 'nullable|string',
            'footerText' => 'nullable|string',
        ]);

        // Map camelCase to snake_case for database
        $mapping = [
            'siteName' => 'site_name',
            'siteDescription' => 'site_description',
            'contactEmail' => 'contact_email',
            'contactPhone' => 'contact_phone',
            'address' => 'address',
            'logo' => 'logo',
            'favicon' => 'favicon',
            'footerText' => 'footer_text',
        ];

        foreach ($mapping as $camelKey => $snakeKey) {
            if (isset($validated[$camelKey])) {
                SiteSetting::set($snakeKey, $validated[$camelKey], 'text');
            }
        }

        // Handle social links
        if (isset($validated['socialLinks'])) {
            foreach ($validated['socialLinks'] as $platform => $url) {
                SiteSetting::set('social_' . $platform, $url, 'text');
            }
        }

        return $this->successResponse(null, 'Settings updated successfully');
    }
}
