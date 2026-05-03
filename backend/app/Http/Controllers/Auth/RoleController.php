<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Role::select('id', 'name', 'description')->get()]);
    }

    public function assign(Request $request, int $userId)
    {
        $request->validate(['roles' => ['required','array'], 'roles.*' => ['string']]);
        $user = User::findOrFail($userId);
        $ids = Role::whereIn('name', $request->roles)->pluck('id')->all();
        $user->roles()->syncWithoutDetaching($ids);
        return response()->json(['message' => 'Roles assigned']);
    }

    public function revoke(Request $request, int $userId)
    {
        $request->validate(['roles' => ['required','array'], 'roles.*' => ['string']]);
        $user = User::findOrFail($userId);
        $ids = Role::whereIn('name', $request->roles)->pluck('id')->all();
        $user->roles()->detach($ids);
        return response()->json(['message' => 'Roles revoked']);
    }
}
