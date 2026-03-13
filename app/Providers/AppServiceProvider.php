<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('settings')) {

            $settings = Setting::pluck('value', 'key')->toArray();

            View::share('settings', $settings);

            Config::set('mail.default', $settings['mail_mailer'] ?? 'smtp');

            Config::set('mail.mailers.smtp.host', $settings['smtp_host'] ?? '');
            Config::set('mail.mailers.smtp.port', $settings['smtp_port'] ?? 587);
            Config::set('mail.mailers.smtp.encryption', $settings['smtp_encryption'] ?? 'tls');
            Config::set('mail.mailers.smtp.username', $settings['smtp_user'] ?? '');
            Config::set('mail.mailers.smtp.password', $settings['smtp_pass'] ?? '');

            Config::set('mail.from.address', $settings['mail_from_address'] ?? '');
            Config::set('mail.from.name', $settings['mail_from_name'] ?? '');
        }
    }
}
