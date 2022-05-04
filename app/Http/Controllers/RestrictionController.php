<?php

namespace App\Http\Controllers;

use App\Models\Restriction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class RestrictionController extends Controller
{
    /**
     * Return restrictions in a GeoJson format
     * @param Request $request
     * @return BaseResponse
     * @throws JsonException
     */
    public function index(Request $request): BaseResponse
    {
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
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Display the specified resource.
     *
     * @param Restriction $restriction
     * @return JsonResponse
     */
    public function show(Restriction $restriction): JsonResponse
    {
        return response()->json($restriction, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Restriction $restriction
     * @return JsonResponse
     */
    public function update(Request $request, Restriction $restriction)
    {
        return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Restriction $restriction
     * @return JsonResponse
     */
    public function destroy(Restriction $restriction)
    {
        return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
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
