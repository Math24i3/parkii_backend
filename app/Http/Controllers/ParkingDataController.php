<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 *
 */
class ParkingDataController extends Controller
{
    /**
     * @param Request $request
     * @return BaseResponse
     * @throws JsonException
     */
    public function zones(Request $request): BaseResponse {
        if ($cache = Cache::get('zones')) {
            $json = $cache;
        } else {
            $zones = Storage::disk('do')->get('parking-data/zone_data.json');
            if (!$zones) {
                return response()->json([
                    'message' => 'Requested data was not found'
                ], BaseResponse::HTTP_NOT_FOUND);
            }
            $json = json_decode($zones, true, 512, JSON_THROW_ON_ERROR);
            Cache::put('zones', $json, now()->addMinutes(10));
        }
        return response()->json($json, BaseResponse::HTTP_OK);
    }
}
