<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OtpService;
use App\Services\SubIdService;
use App\Services\AccessControlService;
use App\Services\WhatsAppService;
use App\Services\PdfService;
use App\Services\ReminderService;
use App\Services\ExcelExportService;
use App\Services\RazorpayService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind all services as singletons — one instance per request
        $this->app->singleton(OtpService::class);
        $this->app->singleton(SubIdService::class);
        $this->app->singleton(WhatsAppService::class);
        $this->app->singleton(PdfService::class);
        $this->app->singleton(ExcelExportService::class);
        $this->app->singleton(RazorpayService::class);

        // ReminderService depends on WhatsAppService — inject it
        $this->app->singleton(ReminderService::class, function ($app) {
            return new ReminderService($app->make(WhatsAppService::class));
        });

        // AccessControlService depends on Otp + WhatsApp
        $this->app->singleton(AccessControlService::class, function ($app) {
            return new AccessControlService(
                $app->make(OtpService::class),
                $app->make(WhatsAppService::class),
            );
        });
    }

    public function boot(): void
    {
        // Enforce HTTPS in production
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
