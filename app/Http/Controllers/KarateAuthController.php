<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KarateAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ]);

        try {

            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post('https://api-members.ikak.net/api/auth/login', [
                    'email' => trim($request->username),
                    'password' => $request->password,
                ]);

            $result = $response->json();

            if (!$response->successful() || !($result['result'] ?? false)) {

                return back()
                    ->with('error', $result['message'] ?? 'Invalid credentials')
                    ->withInput($request->only('username'));
            }

            $apiUser = $result['data']['user'];



            session([
                'logged_in'          => true,
                'ikak_token'         => $result['data']['token'] ?? null,
                'ikak_refresh_token' => $result['data']['refreshToken'] ?? null,
                'ikak_user'          => $apiUser,
                'user_name'          => $apiUser['name'] ?? '',
                'user_email'         => $apiUser['email'] ?? trim($request->username),

                // Store role
                'user_role'          => $apiUser['role'] ?? '',

                // Store as array for middleware
                'ikak_roles'         => [$apiUser['role'] ?? ''],
            ]);

            return redirect('/home');

        } catch (\Throwable $e) {

            Log::error('IKAK LOGIN ERROR', [
                'username' => $request->username,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Login failed. Please try again.')
                ->withInput($request->only('username'));
        }
    }

    public function logout()
    {
        session()->flush();

        return redirect('/home');
    }
}