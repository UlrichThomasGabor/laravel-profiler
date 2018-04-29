<?php

namespace JKocik\Laravel\Profiler;

use Illuminate\Foundation\Application;
use JKocik\Laravel\Profiler\Contracts\Profiler;
use JKocik\Laravel\Profiler\Contracts\DataTracker;
use JKocik\Laravel\Profiler\Contracts\DataProcessor;
use JKocik\Laravel\Profiler\Contracts\ExecutionWatcher;
use JKocik\Laravel\Profiler\Contracts\RequestHandledListener;

class LaravelProfiler implements Profiler
{
    /**
     * @param Application $app
     * @param DataTracker $dataTracker
     * @param DataProcessor $dataProcessor
     * @param ExecutionWatcher $executionWatcher
     * @return void
     */
    public function boot(
        Application $app,
        DataTracker $dataTracker,
        DataProcessor $dataProcessor,
        ExecutionWatcher $executionWatcher
    ): void {
        $executionWatcher->watch();

        $dataTracker->track();

        $app->terminating(function () use ($dataTracker, $dataProcessor) {
            $dataTracker->terminate();
            $dataProcessor->process($dataTracker);
        });
    }
}
