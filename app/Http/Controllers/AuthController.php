<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 *
 */
class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * @param Request $request
     * @return BaseResponse
     */
    public function register(Request $request): BaseResponse
    {
        $validFields = $request->only([
            'name',
            'email',
            'password',
            'phone',
            'license_plate'
        ]);

        $validator = Validator::make($validFields, [
            'name' => 'required|string|max:255|min:1',
            'email' => 'required|string|email|max:255|min:1|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:8',
            'license_plate' => 'nullable|string|min:2|max:7'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), BaseResponse::HTTP_BAD_REQUEST);
        }

        $user = User::create([
            'name' => $validFields['name'],
            'email' => $validFields['email'],
            'password' => Hash::make($validFields['password']),
            'phone' => $validFields['phone'] ?? null,
            'car' => $validFields['car'] ?? null
        ]);

        if (!$user) {
            return response()->json([
                'message' => 'user could not be created',
            ], BaseResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        //$token = $user->createToken('auth_token')->plainTextToken;

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token, BaseResponse::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return BaseResponse
     */
    public function login(Request $request): BaseResponse
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }

    /**
     * @param Request $request
     * @return BaseResponse
     */
    public function user(Request $request): BaseResponse
    {
        $user = $request->user();
        return response()->json([
            'user' => $user,
        ], BaseResponse::HTTP_OK);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return BaseResponse
     */
    public function logout(): BaseResponse
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out'], BaseResponse::HTTP_OK);
    }
    /**
     * Refresh a token.
     *
     * @return BaseResponse
     */
    public function refresh(): BaseResponse
    {
        return $this->createNewToken(auth()->refresh());
    }
    /**
     * Get the authenticated User.
     *
     * @return BaseResponse
     */
    public function userProfile(): BaseResponse
    {
        return response()->json(auth()->user(), BaseResponse::HTTP_OK);
    }
    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return BaseResponse
     */
    protected function createNewToken(string $token, int $responseStatus = BaseResponse::HTTP_OK): BaseResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ], $responseStatus);
    }
}
