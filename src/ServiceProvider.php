<?php

namespace JKocik\Laravel\Profiler;

use ElephantIO\EngineInterface;
use ElephantIO\Engine\SocketIO\Version2X;
use JKocik\Laravel\Profiler\Contracts\Profiler;
use JKocik\Laravel\Profiler\Contracts\DataTracker;
use JKocik\Laravel\Profiler\Contracts\DataProcessor;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(static::profilerConfigPath(), 'profiler');

        $this->app->singleton(DataTracker::class, function () {
            return new LaravelDataTracker();
        });

        $this->app->singleton(DataProcessor::class, function () {
            return new LaravelDataProcessor();
        });

        $this->app->singleton(EngineInterface::class, function () {
            return new Version2X(ProfilerConfig::broadcastingUrl());
        });

        $this->app->singleton(Profiler::class, function () {
            return ProfilerResolver::resolve($this->app);
        });
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->allowConfigFileToBePublished();

        $this->app->make(Profiler::class)->boot(
            $this->app->make(DataTracker::class),
            $this->app->make(DataProcessor::class)
        );
    }

    /**
     * @return void
     */
    public function allowConfigFileToBePublished(): void
    {
        $this->publishes([
            static::profilerConfigPath() => config_path('profiler.php'),
        ]);
    }

    /**
     * @return string
     */
    public static function profilerConfigPath(): string
    {
        return __DIR__ . '/../config/profiler.php';
    }
}