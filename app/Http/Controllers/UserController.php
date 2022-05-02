<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 *
 */
class UserController extends Controller
{
    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param $user
     * @return Response
     */
    public function show($user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $user
     * @return JsonResponse
     */
    public function update(Request $request, $user): JsonResponse
    {
        $validatedData = $request->validate([
            'name'              => 'string|max:255|min:1',
            'email'             => 'string|email|max:255|min:1|unique:users',
            'phone'             => 'nullable|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:8',
            'license_plate'     => 'nullable|string|min:2|max:7'
        ]);

        $updateResponse = $this->userRepository->update($user, $validatedData);

        if (!$updateResponse) {
            $response = [
                'message' => 'Update failed'
            ];
            return response()->json($response, BaseResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        $response = [
            'message' => 'User was updated',
            'user' => $updateResponse
        ];
        return response()->json($response, BaseResponse::HTTP_OK);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $user
     * @return JsonResponse
     */
    public function destroy($user): JsonResponse
    {
        $currentUser = Auth::id();

        if (!$currentUser) {
            $response = [
                'message' => 'Unauthorized'
            ];
            return response()->json($response, BaseResponse::HTTP_UNAUTHORIZED);
        }

        if ($currentUser !== (int)$user) {
            $response = [
                'message' => "You don't have permission to do this"
            ];
            return response()->json($response, BaseResponse::HTTP_UNAUTHORIZED);
        }

        $delete = User::destroy($user);

        if (!$delete) {
            $response = [
                'message' => 'An error occurred while deleting account'
            ];
            return response()->json($response, BaseResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = [
            'message' => 'User was deleted successfully'
        ];
        return response()->json($response, BaseResponse::HTTP_OK);
    }
}
