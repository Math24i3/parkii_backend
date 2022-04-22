<?php

namespace App\Http\Controllers;

use App\Services\CdnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as BaseResponse;


/**
 *
 */
class DOSpacesController extends Controller
{

    /**
     * @param Request $request
     * @return BaseResponse
     */
    public function store(Request $request): BaseResponse
    {
        if (!$file = $request->file('file')) {
            return response()->json([
                'message' => 'file was not in request'
            ], BaseResponse::HTTP_BAD_REQUEST);
        }
        $validator = Validator::make(
            $request->all(), [
            'file' => [
                'required',
                'max:2000',
                'mimes:jpeg,jpg,png,json'
            ],
            'path' => [
                'string',
                'nullable',
                'max:155'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'validation failed',
                'errors' => $validator->errors()
            ], BaseResponse::HTTP_BAD_REQUEST);
        }

        $stored = $file->store(
            $request['path'] ?: '', 'do'
        );

        if (!$stored) {
            return response()->json([
                'message' => 'File could not be uploaded'
            ], BaseResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['message' => 'works'], BaseResponse::HTTP_OK);
    }

}
