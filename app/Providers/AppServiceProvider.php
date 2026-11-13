<?php

namespace App\Providers;

use App\Enums\SettingKey;
use App\Models\AuditActivity;
use App\Services\ProjectSettings;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Activitylog\Actions\LogActivityAction;
use Spatie\Activitylog\Contracts\Activity;

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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        $this->configureAudit();
        $this->configureRootView();
        $this->configurePasswords();
    }

    /** The head of every page (title and description) comes from the project settings, so the tab is right before the app has loaded. */
    private function configureRootView(): void
    {
        View::composer('app', function ($view): void {
            $settings = $this->app->make(ProjectSettings::class);

            $view->with('brand', [
                'name' => $settings->appName(),
                'description' => $settings->value(SettingKey::MetaDescription),
                'favicon' => $settings->imageUrl(SettingKey::Favicon),
            ]);
        });
    }

    /** D-0031: 8 characters (D-0001 asked for 15); the breach-list check needs the network, so it runs in production only. */
    private function configurePasswords(): void
    {
        Password::defaults(function (): Password {
            $rule = Password::min(8)->max(128);

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });
    }

    /** Stamps every audit entry, including the automatic ones from models, with its request. */
    private function configureAudit(): void
    {
        LogActivityAction::beforeLogging(function (Activity $activity): void {
            if (! $activity instanceof AuditActivity) {
                return;
            }

            $activity->request_id = Context::get('request_id');
            $activity->ip_address = Context::getHidden('ip_address');
            $activity->method = Context::getHidden('request_method');
            $activity->path = Context::getHidden('request_path');
            $activity->user_agent = Context::getHidden('user_agent');
        });
    }
}
