<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Ulid\Ulid;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $user = User::where('email',  $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => ['code' => 'invalid_username_or_password', 'message' => 'Invalid username or password']], Response::HTTP_BAD_REQUEST);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'ulid' => $user->ulid,
            'name' => $user->name,
            'surname' => $user->surname,
            'token' => $token,
        ], Response::HTTP_OK);
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|min:4|max:32',
                'surname' => 'required|string|min:4|max:32',
                'email' => 'required|string|email|max:125',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => ['code' => 'invalid_registration_input', 'message' => 'Invalid input data for user registration. Please check your input.']], Response::HTTP_BAD_REQUEST);
        }

        $alreadyExists = User::where('email', $validatedData['email'])->exists();
        if ($alreadyExists) {
            return response()->json(['error' => ['code' => 'email_already_taken', 'message' => 'The email address is already taken.']], Response::HTTP_BAD_REQUEST);
        }

        $ulid = Ulid::generate(true);

        $user = User::create([
            'ulid' => (string)$ulid,
            'name' => $validatedData['name'],
            'surname' => $validatedData['surname'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // return response()->json([
        //     'ulid' => $user->ulid,
        //     'name' => $user->name,
        //     'surname' => $user->surname,
        //     'email' => $user->email,
        // ]);
        return response()->json(['message' => 'User registered sucessfully.'], Response::HTTP_OK);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successful'], Response::HTTP_OK);
    }
}
