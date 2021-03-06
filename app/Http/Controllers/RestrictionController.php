<?php

namespace App\Http\Controllers;

use App\Models\Restriction;
use App\Services\RestrictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class RestrictionController extends Controller
{

    protected RestrictionService $restrictionService;

    /**
     * @param RestrictionService $restrictionService
     */
    public function __construct(RestrictionService $restrictionService)
    {
        $this->restrictionService = $restrictionService;
    }


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
            'distance',
            'restrictionIds'
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
            ],
            'restrictionIds' => [
                'array',
                'nullable'
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

        if (isset($validFields['restrictionIds'])) {
            $restrictions = $this->restrictionService->restrictionsByIds($validFields['restrictionIds']);
            if (!$restrictions) {
                return response()->json([
                    'message' => 'Requested data was not found'
                ], BaseResponse::HTTP_NOT_FOUND);
            }
            return response()->json($restrictions, BaseResponse::HTTP_OK);
        }
        if ($cache = Cache::get('restrictions')) {
            $json = $cache;
        } else {
            $restrictions = Storage::disk('do')->get('parking-data/restrictions.json');
            if (!$restrictions) {
                return response()->json([
                    'message' => 'Requested data was not found'
                ], BaseResponse::HTTP_NOT_FOUND);
            }
            $json = json_decode($restrictions, true, 512, JSON_THROW_ON_ERROR);
            Cache::put('restrictions', $json, now()->addMinutes(10));
        }

        if (isset($validFields['latitude'], $validFields['longitude'])) {
            $distanceLimit = $validFields['distance'] ?? 1;
            foreach ($json['features'] as $key => $feature) {
                foreach ($feature['geometry']['coordinates'][0] as $coords) {
                    $distance = RestrictionService::calculateDistance(
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
            $json['features'] = array_values($json['features']);
            $json['totalFeatures'] = count($json['features']);
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
        return response()->json(['message' => 'POST not supported'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Display the specified resource.
     *
     * @param Restriction $restriction
     * @return JsonResponse
     */
    public function show(Restriction $restriction): JsonResponse
    {
        $result = $this->restrictionService->determineRestriction($restriction);

        return response()->json($result, Response::HTTP_OK);
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
        return response()->json(['message' => 'PUT not supported'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Restriction $restriction
     * @return JsonResponse
     */
    public function destroy(Restriction $restriction)
    {
        return response()->json(['message' => 'DELETE not supported'], Response::HTTP_UNAUTHORIZED);
    }
}
