<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ResponseTrait;

    public function register(Request $request)
    {
        try {
            $user = AuthService::register($request);

            return $this->responseJSON($user, "Registered successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $user = AuthService::login($request);

            if (!$user) {
                return $this->responseJSON(null, "Invalid credentials.", 401);
            }

            return $this->responseJSON($user, "Logged in successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, "Login failed.", 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->responseJSON(null, "Logged out successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, "Logout failed.", 500);
        }
    }
}