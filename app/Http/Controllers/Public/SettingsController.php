<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Traits\ApiResponse;

class SettingsController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $publicSettings = [
            'siteName' => SiteSetting::get('site_name', 'Smatatech Technologies'),
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

        return $this->successResponse($publicSettings);
    }
}
