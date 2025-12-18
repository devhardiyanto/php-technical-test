<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BetterUserController extends Controller
{
    use ApiResponse;

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

        $users = $query->paginate(10);

        $data = UserResource::collection($users);

        // Construct custom pagination meta to match sample
        $pagination = [
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'success',
            'code' => 200,
            'data' => $data,
            'meta' => [
                'pagination' => $pagination
            ]
        ]);
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
            'password' => $validated['password'],
            'role' => 'user',
            'active' => true,
        ]);

        // Mock sending emails
        Log::info("Sending welcome email to {$user->email}");
        Log::info("Sending new user notification to admin");

        return $this->successResponse(new UserResource($user), 'success', 201);
    }
}
