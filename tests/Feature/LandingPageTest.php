<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_renders_without_framework_version(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(config('app.name', 'FaceID Absen'));
        $response->assertDontSee(app()->version());
    }
}
