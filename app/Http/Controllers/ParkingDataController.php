<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
        $zones = Storage::disk('do')->get('parking-data/zone_data.json');
        if (!$zones) {
            return response()->json([
                'message' => 'Requested data was not found'
            ], BaseResponse::HTTP_NOT_FOUND);
        }
        $json = json_decode($zones, true, 512, JSON_THROW_ON_ERROR);

        return response()->json($json, BaseResponse::HTTP_OK);
    }

    /**
     * Return restrictions in a GeoJson format
     * @param Request $request
     * @return BaseResponse
     * @throws JsonException
     */
    public function restrictions(Request $request): BaseResponse {
        // Only valid fields are allowed to pass through
        $validFields = $request->only([
            'latitude',
            'longitude',
            'distance'
        ]);

        // Validating the request data
        $requestValidator = Validator::make($validFields, [
            'latitude' => [
                'numeric',
                'nullable',
            ],
            'longitude' => [
                'numeric',
                'nullable',
            ],
            'distance' => [
                'numeric',
                'nullable',
            ]
        ]);

        if ($requestValidator->fails()) {
            $response = [
                'message' => 'validation failed',
                'errors' => $requestValidator->errors()
                    ->all()
            ];
            return response()->json($response, BaseResponse::HTTP_BAD_REQUEST);
        }

        $restrictions = Storage::disk('do')->get('parking-data/restrictions.json');

        if (!$restrictions) {
            return response()->json([
                'message' => 'Requested data was not found'
            ], BaseResponse::HTTP_NOT_FOUND);
        }
        $json = json_decode($restrictions, true, 512, JSON_THROW_ON_ERROR);

        if (isset($validFields['latitude'], $validFields['longitude'])) {
            $distanceLimit = $validFields['distance'] ?? 1;
            foreach ($json['features'] as $key => $feature) {
                foreach ($feature['geometry']['coordinates'][0] as $coords) {
                    $distance = $this->calculateDistance(
                        [
                            'latitude' => $validFields['latitude'],
                            'longitude' => $validFields['longitude']
                        ],
                        [
                            'longitude' => $coords[0],
                            'latitude' => $coords[1]
                        ]
                    );
                    if ($distance > $distanceLimit) {
                        unset($json['features'][$key]);
                        break;
                    }
                }
            }
            //Reindexing the features
            $json['features']       = array_values($json['features']);
            $json['totalFeatures']  = count($json['features']);
        }
        return response()->json($json, BaseResponse::HTTP_OK);
    }

    /**
     * Returns the distance to and from in kilometers
     * @param array $from
     * @param array $to
     * @return string
     */
    private function calculateDistance(array $from, array $to): string
    {
        //Calculate distance from latitude and longitude
        $theta = $from['longitude'] - $to['longitude'];
        $dist = sin(deg2rad($from['latitude'])) *
                sin(deg2rad($to['latitude'])) +
                cos(deg2rad($from['latitude'])) *
                cos(deg2rad($to['latitude'])) *
                cos(deg2rad($theta));

        $dist = acos($dist);
        $dist = rad2deg($dist);

        return (($dist * 60 * 1.1515) * 1.609344);
    }
}
