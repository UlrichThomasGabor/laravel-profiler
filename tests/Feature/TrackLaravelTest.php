<?php

namespace JKocik\Laravel\Profiler\Tests\Feature;

use Mockery;
use ElephantIO\EngineInterface;
use Illuminate\Foundation\Application;
use JKocik\Laravel\Profiler\Tests\TestCase;
use JKocik\Laravel\Profiler\Tests\Support\Fixtures\TrackerA;
use JKocik\Laravel\Profiler\Tests\Support\Fixtures\TrackerB;
use JKocik\Laravel\Profiler\Tests\Support\Fixtures\ProcessorA;
use JKocik\Laravel\Profiler\Tests\Support\Fixtures\ProcessorB;

class TrackLaravelTest extends TestCase
{
    /** @test */
    function collected_data_are_processed_when_laravel_is_terminated()
    {
        $this->app = $this->appWith(function (Application $app) {
            $app->make('config')->set('profiler.trackers', [
                TrackerA::class,
                TrackerB::class,
            ]);
            $app->make('config')->set('profiler.processors', [
                ProcessorA::class,
                ProcessorB::class,
            ]);
            $app->singleton(ProcessorA::class, function () {
               return new ProcessorA();
            });
            $app->singleton(ProcessorB::class, function () {
                return new ProcessorB();
            });
        });

        $processorA = $this->app->make(ProcessorA::class);
        $processorB = $this->app->make(ProcessorB::class);

        $this->assertNotEquals('meta-value', $processorA->meta->get('meta-key'));
        $this->assertNotEquals('meta-value', $processorB->meta->get('meta-key'));
        $this->assertNotEquals('data-value', $processorA->data->get('data-key'));
        $this->assertNotEquals('data-value', $processorB->data->get('data-key'));

        $this->app->terminate();

        $this->assertEquals('meta-value', $processorA->meta->get('meta-key'));
        $this->assertEquals('meta-value', $processorB->meta->get('meta-key'));
        $this->assertEquals('data-value', $processorA->data->get('data-key'));
        $this->assertEquals('data-value', $processorB->data->get('data-key'));
    }

    /** @test */
    function collected_data_are_broadcast_by_default()
    {
        $socketEngine = Mockery::mock(EngineInterface::class);
        $socketEngine->shouldReceive('connect')->once();
        $socketEngine->shouldReceive('close')->once();
        $socketEngine->shouldNotReceive('keepAlive');
        $socketEngine->shouldReceive('emit')->withArgs(['laravel-profiler-broadcasting', [
            'meta' => collect(),
            'data' => collect()
        ]])->once();

        $this->app->singleton(EngineInterface::class, function () use ($socketEngine) {
            return $socketEngine;
        });

        $this->app->terminate();

        $this->assertSame($socketEngine, $this->app->make(EngineInterface::class));
    }
}
