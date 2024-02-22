<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Ulid\Ulid;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $user = User::where('email',  $request->email)->first();

        if (!$user) {    
            return response()->json(['message' => ['Invalid email or username']], 422);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => ['Invalid password']], 422);
        }

        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User logged in successfully',
            'ulid' => $user->ulid,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Email is already taken.'], 422);
        }

        $ulid = Ulid::generate(true);

        $user = User::create([
            'ulid' => (string)$ulid,
            'name' => $validatedData['name'],
            'surname' => $validatedData['surname'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        return response()->json([
            'ulid' => $user->ulid,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(
            [
                'status' => 'success',
                'message' => 'User logged out successfully'
            ]
        );
    }
}
