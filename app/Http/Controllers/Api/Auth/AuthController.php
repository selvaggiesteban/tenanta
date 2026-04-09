<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return $this->respondError('Credenciales inválidas', 401);
        }

        $user = auth('api')->user();

        // Check if tenant is active
        if ($user->tenant && $user->tenant->trashed()) {
            auth('api')->logout();
            return $this->respondError('Tu cuenta ha sido desactivada', 403);
        }

        $user->update(['last_login_at' => now()]);

        return $this->respondWithToken($token, $user);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $request->company_name,
                'slug' => Str::slug($request->company_name) . '-' . Str::lower(Str::random(6)),
                'trial_ends_at' => now()->addDays(14),
                'settings' => [
                    'timezone' => 'America/Argentina/Buenos_Aires',
                    'language' => 'es',
                ],
            ]);

            // Create admin user
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'admin',
                'email_verified_at' => now(),
                'accepted_privacy_at' => now(),
                'subscribed_to_newsletter' => $request->boolean('subscribed_to_newsletter', false),
            ]);

            $token = auth('api')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registro exitoso',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'trial_ends_at' => $tenant->trial_ends_at,
                ],
            ], 201);
        });
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user();
        $user->load('tenant');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'contracted_hours' => $user->contracted_hours,
                'billable_rate' => $user->billable_rate,
                'timezone' => $user->timezone,
                'avatar_url' => $user->avatar_url,
                'email_verified_at' => $user->email_verified_at,
                'last_login_at' => $user->last_login_at,
                'tenant' => [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'slug' => $user->tenant->slug,
                    'logo_url' => $user->tenant->logo_url,
                    'primary_color' => $user->tenant->primary_color,
                    'trial_ends_at' => $user->tenant->trial_ends_at,
                ],
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    public function refresh(): JsonResponse
    {
        $token = auth('api')->refresh();
        $user = auth('api')->user();

        return $this->respondWithToken($token, $user);
    }

    protected function respondWithToken(string $token, User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'tenant_id' => $user->tenant_id,
            ],
        ]);
    }
}
