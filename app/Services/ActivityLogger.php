<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Log an activity.
     */
    public static function log(
        string $type,
        string $title,
        ?string $description = null,
        ?Model $actor = null,
        ?Model $subject = null,
        array $properties = []
    ): ActivityLog {
        return ActivityLog::log($type, $title, $description, $actor, $subject, $properties);
    }

    /**
     * Log a post activity.
     */
    public static function postCreated(Model $post, Model $actor): ActivityLog
    {
        return self::log('post_created', 'Blog post created', "Post '{$post->title}' was created", $actor, $post);
    }

    /**
     * Log a user registration.
     */
    public static function userRegistered(Model $user): ActivityLog
    {
        return self::log('user_registered', 'New user registered', "{$user->name} registered an account", $user, $user);
    }

    /**
     * Log a contact form submission.
     */
    public static function contactReceived(Model $contact): ActivityLog
    {
        return self::log('contact_received', 'New contact message', "Contact form submitted by {$contact->name}", null, $contact);
    }

    /**
     * Log admin login.
     */
    public static function adminLogin(Model $admin): ActivityLog
    {
        return self::log('admin_login', 'Admin logged in', "{$admin->name} logged in to the admin panel", $admin);
    }

    /**
     * Log settings update.
     */
    public static function settingsUpdated(Model $actor, string $settingType): ActivityLog
    {
        return self::log('settings_updated', "{$settingType} settings updated", "{$settingType} configuration was updated", $actor);
    }
}
