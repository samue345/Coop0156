<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebSecurityHeadersTest extends TestCase
{
    public function test_it_adds_security_headers_to_web_pages(): void
    {
        $response = $this->get('/')
            ->assertOk();

        foreach ($this->securityHeaders() as $header => $value) {
            $response->assertHeader($header, $value);
        }
    }

    /**
     * @return array<string, string>
     */
    private function securityHeaders(): array
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'no-referrer',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ];
    }
}
