<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 *
 */
class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name'          => 'required|string|max:255|min:1',
            'email'         => 'required|string|email|max:255|min:1|unique:users',
            'password'      => 'required|string|min:8',
            'phone'         => 'nullable|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:8',
            'license_plate' => 'nullable|string|min:2|max:7'
        ]);

        $user = User::create([
            'name'      => $validatedData['name'],
            'email'     => $validatedData['email'],
            'password'  => Hash::make($validatedData['password']),
            'phone'     => $validatedData['phone'] ?? null,
            'car'       => $validatedData['car']  ?? null
        ]);

        if (!$user) {
            return response()->json([
                'message' => 'user could not be created',
            ], BaseResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token'  => $token,
            'token_type'    => 'Bearer',
        ], BaseResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return BaseResponse
     */
    public function login(Request $request): BaseResponse
    {

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login details'
            ], BaseResponse::HTTP_UNAUTHORIZED);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token'  => $token,
            'token_type'    => 'Bearer',
            'user'          => $user
        ], BaseResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => $user,
        ], BaseResponse::HTTP_OK);
    }
}
