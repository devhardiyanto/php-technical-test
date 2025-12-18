<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::active()->withCount('orders');

        $query->filter($request->only('search'));

        $sortBy = $request->input('sortBy', 'created_at');
        $allowedSorts = ['name', 'email', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy);
        } else {
            $query->orderBy('created_at');
        }

        $users = $query->paginate(10); // Default page size per Laravel or requirement? "page (integer, optional, default: 1)" usually implies pagination.

        // Transform collection to match requirement
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'orders_count' => $user->orders_count,
                'can_edit' => $user->can_edit,
            ];
        });

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'name' => ['required', 'string', 'min:3', 'max:50'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Model casts to hashed
            'role' => 'user', // Default
            'active' => true, // Default
        ]);

        // Mock sending emails
        // 1. To new user
        Log::info("Sending welcome email to {$user->email}");
        // 2. To system administrator
        Log::info("Sending new user notification to admin");

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'created_at' => $user->created_at,
        ], 201);
    }
}
