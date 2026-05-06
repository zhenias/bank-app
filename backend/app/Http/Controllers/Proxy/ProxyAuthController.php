<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProxyAuthController extends Controller
{
    public function redirect()
    {
        $query = http_build_query([
            'client_id'     => config('services.passport.client_id'),
            'redirect_uri'  => config('services.passport.redirect_uri'),
            'response_type' => 'code',
            'scope'         => 'user-profile',
            'state'         => $state = Str::random(40),
        ]);

        session(['oauth_state' => $state]);

        // ✅ Dodaj /oauth/authorize tutaj
        return redirect('/oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        if ($request->state !== session('oauth_state')) {
            abort(403, 'Invalid state');
        }

        // ✅ Użyj url() helpera zamiast config
        $response = Http::asForm()->post(url('/oauth/token'), [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('services.passport.client_id'),
            'client_secret' => config('services.passport.client_secret'),
            'redirect_uri'  => config('services.passport.redirect_uri'),
            'code'          => $request->code,
        ]);

        if (!$response->successful()) {
            abort(401, 'Token exchange failed: ' . $response->body());
        }

        $tokens = $response->json();

        session([
            'access_token'  => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
        ]);

        // ✅ Użyj url() helpera
        $userResponse = Http::withToken($tokens['access_token'])
            ->get(url('/api/user'));

        if ($userResponse->successful()) {
            auth()->loginUsingId($userResponse['id']);
        }

        return redirect('/dashboard');
    }

    public function user(Request $request)
    {
        return $request->user();
    }
}
