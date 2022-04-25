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
        $json = json_decode($zones, true, 512, JSON_THROW_ON_ERROR);
        //$feature1 = $json['features'][0]['properties']['kategori'];
        $distances = [];
        foreach ($json['features'] as $feature) {
            foreach ($feature['geometry']['coordinates'][0][0] as $coord) {
                $distances[] = $this->calculateDistance([$coord[1], $coord[0]]);
            }
        }
        return response()->json($json, BaseResponse::HTTP_OK);
    }

    private function calculateDistance(array $to): string
    {
        $from = ['latitude' => 55.46, 'longitude' => 8.45];
        //Calculate distance from latitude and longitude
        $theta = $from['longitude'] - $to[1];
        $dist = sin(deg2rad($from['latitude'])) *
            sin(deg2rad($to[0])) +
            cos(deg2rad($from['latitude'])) *
            cos(deg2rad($to[0])) *
            cos(deg2rad($theta));

        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return ($miles * 1.609344).' km';
    }

    /**
     * @param Request $request
     * @return BaseResponse
     * @throws JsonException
     */
    public function restrictions(Request $request): BaseResponse {
        $restrictions = Storage::disk('do')->get('parking-data/restrictions.json');

        if (!$restrictions) {
            return response()->json([
                'message' => 'Requested data was not found'
            ], BaseResponse::HTTP_NOT_FOUND);
        }
        $json = json_decode($restrictions, true, 512, JSON_THROW_ON_ERROR);

        return response()->json($json, BaseResponse::HTTP_OK);
    }
}
