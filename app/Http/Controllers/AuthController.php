<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Log;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Traits\APIResponder;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . " start");
        Log::debug($prefix . " request:\n" . json_encode($request->all(), JSON_PRETTY_PRINT));
        try
        {
            Log::debug($prefix . " validating request");
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);            
        }
        catch(Throwable $e)
        {
            Log::error($prefix . " exception:\n" . $e->getMessage());
            Log::error($prefix . " stop");
            return $this->error('Registration failed', 500);
        }
        

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            Log::error($prefix . " exception:\n" . $e->getMessage());
            Log::error($prefix . " stop");
            return $this->error('Could not create token', 500);
        }

        Log::debug($prefix . " User registered successfully");
        Log::debug($prefix . " stop");
        return $this->success('User registered successfully', [
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . " start");
        Log::debug($prefix . " request:\n" . json_encode($request->all(), JSON_PRETTY_PRINT));

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                Log::warning($prefix . " Invalid credentials");
                Log::warning($prefix . " stop");
                return $this->error('Invalid credentials', 401);
                
            }
        } catch (JWTException $e) {
            Log::error($prefix . " exception:\n" . $e->getMessage());
            Log::error($prefix . " stop");
            return $this->error('Could not create token', 500);
        }

        Log::debug($prefix . " Successfully logged in");
        Log::debug($prefix . " stop");
        return $this->success('User logged in successfully', [
            'token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function logout()
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . " start");
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            Log::error($prefix . " exception:\n" . $e->getMessage());
            Log::error($prefix . " stop");
            return $this->error('Failed to logout, please try again', 500);
        }

        Log::debug($prefix . " Successfully logged out");
        Log::debug($prefix . " stop");
        return $this->success('Successfully logged out');
    }

    public function getUser()
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . " start");
        try {
            $user = Auth::user();
            if (!$user) {
                Log::warning($prefix . " User not found");
                Log::warning($prefix . " stop");
                return response()->json(['error' => 'User not found'], 404);
            }
                Log::debug($prefix . " User found");
                Log::debug($prefix . " stop");
            return response()->json($user);
        } catch (JWTException $e) {
            Log::error($prefix . " exception:\n" . $e->getMessage());
            Log::error($prefix . " stop");
            return $this->error('Failed to fetch user profile', 500);
        }
    }

    public function updateUser(Request $request)
    {
        $prefix = __CLASS__ . '::' . __FUNCTION__ . ' -> ';
        Log::debug($prefix . " start");
        Log::debug($prefix . " request:\n" . json_encode($request->all(), JSON_PRETTY_PRINT));
        try {
            $user = Auth::user();
            $user->update($request->only(['name', 'email']));
            Log::debug($prefix . " User updated succe");
            Log::debug($prefix . " stop");
            return $this->success('User updated successfully', $user->toArray());
        } catch (JWTException $e) {
            Log::error($prefix . " exception:\n" . $e->getMessage());
            Log::error($prefix . " stop");
            return $this->error('Failed to update user', 500);
        }
    }
}