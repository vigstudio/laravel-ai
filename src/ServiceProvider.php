<?php

namespace VigStudio\LaravelAI;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use OpenAI\Laravel\Facades\OpenAI;
use VigStudio\LaravelAI\Commands\Chat;
use VigStudio\LaravelAI\Commands\ImageGenerate;
use VigStudio\LaravelAI\Commands\ImportModels;
use VigStudio\LaravelAI\Connectors\OpenAIConnector;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrations();
        $this->registerCommands();
        $this->mergeConfigurations();
        $this->configureDependencyInjection();
    }

    /**
     * Load the migrations
     */
    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom([
            __DIR__.'/../database/migrations/',
        ]);
    }

    /**
     * Register the commands
     */
    private function registerCommands(): void
    {
        $this->commands([
            Chat::class,
            ImageGenerate::class,
            ImportModels::class,
        ]);
    }

    /**
     * Merge the config
     */
    private function mergeConfigurations(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-ai.php', 'laravel-ai');

        // Override OpenAI config with our config, but keep the original organization
        $openaiConfig = config('openai');
        $openaiConfig['api_key'] = config('laravel-ai.openai.api_key');
        $openaiConfig['default_max_tokens'] = config('laravel-ai.openai.default_max_tokens');
        $openaiConfig['default_temperature'] = config('laravel-ai.openai.default_temperature');
        $this->app['config']->set('openai', $openaiConfig);
    }

    /**
     * Configure the Dependency Injection
     */
    private function configureDependencyInjection(): void
    {
        /**
         * The OpenAI connector
         */
        $this->app->singleton(OpenAIConnector::class, function (Application $app) {
            return ( new OpenAIConnector(
                OpenAI::chat(),
                OpenAI::images(),
                OpenAI::models()
            ) )
                ->withDefaultMaxTokens(config('laravel-ai.openai.default_max_tokens'))
                ->withDefaultTemperature(config('laravel-ai.openai.default_temperature'));
        });
    }
}
