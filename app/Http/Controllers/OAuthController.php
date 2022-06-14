<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $query = http_build_query([
            'client_id' => env('OAUTH_CLIENT_ID'),
            'redirect_uri' => env('OAUTH_CLIENT_URL') . '/oauth/callback',
            'response_type' => 'code',
            'scope' => '',
        ]);

        return redirect(env('OAUTH_SERVER_URL') . '/oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $response = Http::post(env('OAUTH_SERVER_URL') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => env('OAUTH_CLIENT_ID'),
            'client_secret' => env('OAUTH_CLIENT_SECRET'),
            'redirect_uri' => env('OAUTH_CLIENT_URL') . '/oauth/callback',
            'code' => $request->code,
        ]);

        $response = $response->json();

        $request->user()->token()->delete();

        $request->user()->token()->create([
            'access_token' => $response['access_token']
        ]);

        return redirect('/home')->with('status', 'Success. Token expires in: ' . $response['expires_in'] . ' seconds.');
    }
}
