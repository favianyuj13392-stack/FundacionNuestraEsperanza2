<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        try {
            $user = new User();
            $user->name = $data['name'];
            $user->last_name = $data['last_name'] ?? null;
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->ci = $data['ci'] ?? null;
            $user->is_active = true;
            $user->save();

            $roles = $data['roles'] ?? ['viewer'];
            $ids = Role::whereIn('name', $roles)->pluck('id');
            if ($ids->isNotEmpty()) {
                $user->roles()->syncWithoutDetaching($ids->all());
            }

            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'message' => 'Registered successfully',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'roles' => $user->roles()->pluck('name'),
                    ],
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error registering user', [
                'email' => $request->input('email'),
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Internal error registering user'], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
        } catch (\Throwable $e) {
            Log::error('Error querying user during login', [
                'email' => $request->email,
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Internal error logging in'], 500);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'User inactive'], 403);
        }

        try {
            $token = $user->createToken('api')->plainTextToken;
        } catch (\Throwable $e) {
            Log::error('Error generating token in login', [
                'user_id' => $user->id ?? null,
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Internal error logging in'], 500);
        }

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'ci' => $user->ci,
                    'roles' => $user->roles()->pluck('name'),
                ],
            ],
        ]);
    }

    public function me(Request $request)
    {
        $u = $request->user();

        return response()->json([
            'data' => [
                'id' => $u->id,
                'name' => $u->name,
                'last_name' => $u->last_name,
                'email' => $u->email,
                'ci' => $u->ci,
                'roles' => $u->roles()->pluck('name'),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Session closed']);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = $request->user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 401);
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            // Revoke all tokens to force re-login
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Password updated successfully. Please login again.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error changing password', [
                'user_id' => $request->user()->id ?? null,
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Internal error changing password'], 500);
        }
    }
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20', // Add phone if useful, though not strictly requested, good for donors
                'ci' => 'nullable|string|max:20',
            ]);

            $user->update($validated);

            return response()->json([
                'message' => 'Perfil actualizado correctamente',
                'data' => $user
            ]);
        } catch (\Throwable $e) {
             Log::error('Error updating profile', [
                'user_id' => $request->user()->id ?? null,
                'exception' => $e,
            ]);
            return response()->json(['message' => 'Error al actualizar el perfil'], 500);
        }
    }
}
