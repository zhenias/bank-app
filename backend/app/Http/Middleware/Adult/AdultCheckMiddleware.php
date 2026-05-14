<?php

namespace App\Http\Middleware\Adult;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AdultCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(Request): (Response) $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $user = $request->user()->toArray();

        $isAdult        = $user['is_adult'] ?? false;
        $hasGuardian    = isset($user['guardian']['id']) && $user['guardian']['id'] && $user['guardian']['guardian_approved_at'];
        $hasDateOfBirth = isset($user['date_of_birth']) && $user['date_of_birth'];

        if (!$hasDateOfBirth) {
            throw new AccessDeniedHttpException('Access denied. You must set date of birth.');
        }

        if (! $isAdult && ! $hasGuardian) {
            throw new AccessDeniedHttpException('Access denied. You must be 18 or older, or have a legal guardian assigned to your account.');
        }

        return $next($request);
    }
}
