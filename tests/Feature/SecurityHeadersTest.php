<?php

namespace Tests\Feature;

use App\Http\Middleware\AddSecurityHeaders;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_it_adds_security_headers_to_api_responses(): void
    {
        Route::middleware(['api', AddSecurityHeaders::class])->get('/api/security-headers-probe', fn () => response()->json([
            'message' => 'Security headers enabled.',
        ], 202));

        $response = $this->getJson('/api/security-headers-probe')
            ->assertStatus(202);

        foreach ($this->securityHeadersExceptCacheControl() as $header => $value) {
            $response->assertHeader($header, $value);
        }

        foreach ($this->cacheControlDirectives() as $directive) {
            $this->assertStringContainsString($directive, $response->headers->get('Cache-Control'));
        }
    }

    public function test_it_does_not_add_security_headers_without_the_security_headers_middleware(): void
    {
        Route::get('/security-headers-probe', fn () => response()->json([
            'message' => 'Security headers disabled.',
        ], 418));

        $response = $this->get('/security-headers-probe')
            ->assertStatus(418);

        foreach ($this->nonDefaultSecurityHeaders() as $header) {
            $response->assertHeaderMissing($header);
        }
    }

    public function test_it_does_not_add_security_headers_to_the_mock_bureau_response(): void
    {
        $response = $this->getJson('/api/mock/bureau/10000000523')
            ->assertOk();

        foreach ($this->nonDefaultSecurityHeaders() as $header) {
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

    /**
     * @return array<string, string>
     */
    private function securityHeadersExceptCacheControl(): array
    {
        return array_filter(
            $this->securityHeaders(),
            fn (string $header): bool => $header !== 'Cache-Control',
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * @return list<string>
     */
    private function cacheControlDirectives(): array
    {
        return [
            'no-store',
            'no-cache',
            'must-revalidate',
        ];
    }

    /**
     * @return list<string>
     */
    private function nonDefaultSecurityHeaders(): array
    {
        return [
            'X-Content-Type-Options',
            'X-Frame-Options',
            'Referrer-Policy',
            'Permissions-Policy',
        ];
    }
}
