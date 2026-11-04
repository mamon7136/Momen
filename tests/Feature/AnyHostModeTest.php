<?php

namespace Tests\Feature;

use Database\Seeders\PlatformSeeder;
use Tests\TestCase;

/** PLATFORM_DOMAIN=* : everything works on whatever host the site is opened from. */
class AnyHostModeTest extends TestCase
{
    public function test_platform_and_path_stores_work_on_any_host(): void
    {
        $this->refreshApplicationWithAnyHost();
        $store = $this->makeStore('anyhost');

        $this->get('http://203.0.113.7/')->assertOk();
        $this->get('http://203.0.113.7/s/anyhost')->assertOk()->assertSee('Store anyhost');
        $this->assertSame('http://203.0.113.7/s/anyhost', $this->app['url']->to('/s/anyhost'));
        $this->actingAs($store->owner)->get('http://some-preview.example/dashboard')->assertOk()->assertSee('/s/anyhost');
    }

    protected function refreshApplicationWithAnyHost(): void
    {
        putenv('PLATFORM_DOMAIN=*');
        $_ENV['PLATFORM_DOMAIN'] = $_SERVER['PLATFORM_DOMAIN'] = '*';
        $this->refreshApplication();
        $this->artisan('migrate');
        $this->seed(PlatformSeeder::class);
    }

    protected function tearDown(): void
    {
        putenv('PLATFORM_DOMAIN=platform.test');
        $_ENV['PLATFORM_DOMAIN'] = $_SERVER['PLATFORM_DOMAIN'] = 'platform.test';
        parent::tearDown();
    }
}
