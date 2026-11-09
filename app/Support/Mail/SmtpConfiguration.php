<?php

namespace App\Support\Mail;

use App\Models\Admin\SmtpSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;


class SmtpConfiguration
{
    /**
     * Override Laravel's mail configuration from the administrator-managed
     * record. If the feature is not configured/enabled, .env stays authoritative.
     */
    public static function boot(): void
    {
        // During first install the database (or table) may not exist yet.
        // Never prevent artisan migrate / cache commands from starting.
        try {
            if (!Schema::hasTable('smtp_settings')) {
                return;
            }
            $settings = SmtpSetting::query()->first();
        } catch (\Illuminate\Database\QueryException|\PDOException $exception) {
            return;
        }

        if ($settings?->enabled) {
            self::apply($settings);
        }
    }

    public static function apply(SmtpSetting $settings): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $settings->host,
            'mail.mailers.smtp.port' => (int) $settings->port,
            'mail.mailers.smtp.encryption' => $settings->encryption === 'none' ? null : $settings->encryption,
            'mail.mailers.smtp.username' => $settings->username,
            'mail.mailers.smtp.password' => $settings->password,
            'mail.from.address' => $settings->from_address,
            'mail.from.name' => $settings->from_name,
        ]);

        // Prevent stale transports in long-lived processes and after edits.
        Mail::purge('smtp');
    }
}
