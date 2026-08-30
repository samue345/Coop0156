<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_it_adds_security_headers_to_api_responses(): void
    {
        Route::middleware('api')->get('/api/security-headers-probe', fn () => response()->json([
            'message' => 'Security headers enabled.',
        ], 202));

        $response = $this->getJson('/api/security-headers-probe')
            ->assertStatus(202);

        foreach ($this->securityHeaders() as $header => $value) {
            $response->assertHeader($header, $value);
        }
    }

    public function test_it_does_not_add_security_headers_without_the_api_middleware(): void
    {
        Route::get('/security-headers-probe', fn () => response()->json([
            'message' => 'Security headers disabled.',
        ], 418));

        $response = $this->get('/security-headers-probe')
            ->assertStatus(418);

        foreach (array_keys($this->securityHeaders()) as $header) {
            $response->assertHeaderMissing($header);
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
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ];
    }
}
