<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use JsonException;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class ParkingDataController extends Controller
{
    /**
     * @param Request $request
     * @return BaseResponse
     * @throws JsonException
     */
    public function zones(Request $request): BaseResponse {
        $zones = Storage::disk('do')->get('parking-data/zone_data.json');
        if (!$zones) {
            return response()->json([
                'message' => 'Requested data was not found'
            ], BaseResponse::HTTP_NOT_FOUND);
        }
        return response()->json(json_decode($zones, false, 512, JSON_THROW_ON_ERROR), BaseResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return BaseResponse
     * @throws JsonException
     */
    public function restrictions(Request $request): BaseResponse {
        $zones = Storage::disk('do')->get('parking-data/restrictions.json');

        if (!$zones) {
            return response()->json([
                'message' => 'Requested data was not found'
            ], BaseResponse::HTTP_NOT_FOUND);
        }
        return response()->json(json_decode($zones, false, 512, JSON_THROW_ON_ERROR), BaseResponse::HTTP_OK);
    }
}
