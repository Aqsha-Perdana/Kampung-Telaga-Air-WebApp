<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    public function test_security_headers_are_present_on_web_response(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'geolocation=(), camera=(), microphone=()');
    }

    public function test_critical_routes_have_rate_limiter_middleware(): void
    {
        $router = app('router');

        $this->assertContains(
            'throttle:visitor-login',
            $router->getRoutes()->getByName('wisatawan.login.post')->middleware()
        );

        $this->assertContains(
            'throttle:admin-login',
            $router->getRoutes()->getByName('admin.login.submit')->middleware()
        );

        $this->assertContains(
            'throttle:chatbot',
            $router->getRoutes()->getByName('chatbot.send')->middleware()
        );

        $this->assertContains(
            'throttle:checkout-process',
            $router->getRoutes()->getByName('checkout.process')->middleware()
        );
    }
}

