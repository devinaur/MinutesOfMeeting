<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Injeksi header keamanan standar industri pada seluruh respon HTTP aplikasi.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mencegah clickjacking dengan membatasi rendering frame hanya dari origin yang sama
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Mencegah MIME-type sniffing oleh browser
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Membatasi pengiriman informasi referrer hanya ke origin aman yang sama
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Membatasi akses API perangkat keras yang tidak diperlukan aplikasi akuntansi
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
