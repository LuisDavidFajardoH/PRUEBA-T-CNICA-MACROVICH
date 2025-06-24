<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'timezone' => $request->timezone ?? config('app.timezone'),
                'language' => $request->language ?? config('app.locale'),
                'preferences' => [
                    'theme' => 'light',
                    'notifications' => true,
                    'weather_units' => 'metric'
                ]
            ]);

            event(new Registered($user));

            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'timezone' => $user->timezone,
                        'language' => $user->language,
                        'preferences' => $user->preferences,
                        'created_at' => $user->created_at->toISOString(),
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al registrar usuario',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'nullable|boolean'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.']
            ]);
        }

        $user = Auth::user();
        
        // Revoke existing tokens if needed
        if (!$request->boolean('remember')) {
            $user->tokens()->delete();
        }

        $tokenName = 'auth-token-' . now()->timestamp;
        $token = $user->createToken($tokenName)->plainTextToken;

        // Update last login
        $user->update(['last_login_at' => now()]);

        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'timezone' => $user->timezone,
                    'language' => $user->preferred_language ?? 'es',
                    'last_login_at' => $user->last_login_at ? $user->last_login_at->toISOString() : null,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'timezone' => $user->timezone,
                'language' => $user->language,
                'preferences' => $user->preferences,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at' => $user->created_at->toISOString(),
                'last_login_at' => $user->last_login_at?->toISOString(),
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
            'preferences' => 'nullable|array',
            'preferences.theme' => 'nullable|in:light,dark',
            'preferences.notifications' => 'nullable|boolean',
            'preferences.weather_units' => 'nullable|in:metric,imperial,kelvin'
        ]);

        try {
            $user = $request->user();
            $updateData = $request->only(['name', 'timezone', 'language']);

            if ($request->has('preferences')) {
                $preferences = array_merge($user->preferences ?? [], $request->preferences);
                $updateData['preferences'] = $preferences;
            }

            $user->update($updateData);

            Log::info('User profile updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($updateData)
            ]);

            return response()->json([
                'message' => 'Perfil actualizado exitosamente',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'timezone' => $user->timezone,
                    'language' => $user->language,
                    'preferences' => $user->preferences,
                    'updated_at' => $user->updated_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Profile update failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al actualizar perfil',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        Log::info('User logged out successfully', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke all tokens
        $user->tokens()->delete();

        Log::info('User logged out from all devices', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return response()->json([
            'message' => 'Sesión cerrada en todos los dispositivos'
        ]);
    }

    /**
     * Get user's active sessions/tokens
     */
    public function sessions(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokens = $user->tokens()->select(['id', 'name', 'last_used_at', 'created_at'])->get();

        return response()->json([
            'data' => $tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'last_used_at' => $token->last_used_at?->toISOString(),
                    'created_at' => $token->created_at->toISOString(),
                    'is_current' => $token->id === request()->user()->currentAccessToken()->id
                ];
            })
        ]);
    }

    /**
     * Revoke a specific token/session
     */
    public function revokeSession(Request $request, string $tokenId): JsonResponse
    {
        $request->validate([
            'token_id' => 'required|exists:personal_access_tokens,id'
        ]);

        $user = $request->user();
        $token = $user->tokens()->where('id', $tokenId)->first();

        if (!$token) {
            return response()->json([
                'message' => 'Token no encontrado'
            ], 404);
        }

        $token->delete();

        Log::info('Token revoked', [
            'user_id' => $user->id,
            'token_id' => $tokenId
        ]);

        return response()->json([
            'message' => 'Sesión revocada exitosamente'
        ]);
    }
}
