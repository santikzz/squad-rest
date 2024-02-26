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

        if (!$user) {
            return response()->json(['error' => ['code' => 'user_not_registred', 'message' => 'The provided user doesn\'t exist.']], Response::HTTP_BAD_REQUEST);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => ['code' => 'invalid_password', 'message' => 'Invalid password.']], Response::HTTP_BAD_REQUEST);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        // 'token' => $user->createToken('auth_token')->plainTextToken,

        return response()->json([
            'message' => 'Logged in successfully',
            'ulid' => $user->ulid,
            'token' => $token,
        ], Response::HTTP_OK);
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:50',
                'surname' => 'required|string|max:50',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => ['code' => 'invalid_registration_input', 'message' => 'Invalid input data for user registration. Please check your input.']], Response::HTTP_BAD_REQUEST);
        }

        $alreadyExists = User::where('email', $validatedData['email'])->exists();
        if($alreadyExists){
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
        // if (!$request->user()) {
        //     return response()->json(['error' => ['code' => 'unauthenticated', 'message' => 'User is not authenticated. Please log in or provide valid authentication credentials.']], Response::HTTP_UNAUTHORIZED);
        // }

        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successful'], Response::HTTP_OK);
    }
}
