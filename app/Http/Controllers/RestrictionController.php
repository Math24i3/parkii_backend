<?php

namespace App\Http\Controllers;

use App\Models\Restriction;
use App\Services\RestrictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $response = [
            'restriction' => $restriction,
            'rule' => null,
            'parking_allowed' => 'yes',
            'ticket_required' => 'no',
            'limit' => null
        ];

        if ($restriction->restriktion !== "ja") {
            switch ($restriction->p_ordning) {
                case null:
                    break;
                case "3 timers restriktion":
                    $response["rule"] = "Parking is restricted to 3 hours";
                    $response["limit"] = $this->restrictionService->limitedParking(3);
                    break;
                case "Besøgsplads, privat ordning, privat fællesvej":
                case "Besøgsplads":
                    $response["rule"] = "Visiting parking";
                    break;
                case "Ambassade parkering":
                    //TODO if car is of type ambasade then yes
                    $response["rule"] = "Embassy parking";
                    $response["parking_allowed"] = "criteria";
                    break;
                case "Gul betalingszone":
                case "Rød betalingszone":
                case "Grøn betalingszone":
                case "Blå betalingszone":
                    $response["rule"] = "Payment zone";
                    if ($this->restrictionService->isItSunday()) {
                        $response['ticket_required'] = 'no';
                    } else {
                        $response['ticket_required'] = 'yes';
                    }
                    $response["parking_allowed"] = "yes";
                    break;
                case "Delebil parkering":
                    $response["rule"] = "Shared car parking";
                    //TODO if car is of type delebil then yes
                    $response["parking_allowed"] = "criteria";
                    break;
                case "El-Bil plads":
                    $response["rule"] = "Electric car parking";
                    //TODO if car is of type electric car then yes
                    $response["parking_allowed"] = "criteria";
                    break;
                case "Handicap parkering":
                case "Handicap parkering, privat ordning, privat fællesvej":
                    $response["rule"] = "Disabled person parking";
                    //TODO if car is of type disabled person then yes
                    $response["parking_allowed"] = "criteria";
                    break;
                case "Motorcykel parkering":
                    $response["rule"] = "Motorbike parking";
                    if ($this->restrictionService->isItSunday()) {
                        $response['ticket_required'] = 'no';
                    } else {
                        $response['ticket_required'] = 'yes';
                    }
                    $response["parking_allowed"] = "yes";
                    break;
                case '"Off. reguleret':
                case "Off. reguleret, privat grund":
                    $response["rule"] = "Regulated private parking";
                    $response["parking_allowed"] = "no";
                    break;
                case "Privat ordning, privat fællesvej":
                case "Privat ordning, diverse":
                case "Privat ordning":
                case '"Privat ordning':
                    $response["rule"] = "Private parking";
                    $response["parking_allowed"] = "condition";
                    break;
                case "Taxiholdeplads":
                    $response["rule"] = "Taxi parking";
                    //TODO check if car is taxi
                    $response["parking_allowed"] = "no";
                    break;
                case "Turistbus plads":
                    $response["rule"] = "Turist-bus parking";
                    $response["parking_allowed"] = "no";
            }
        }



        return response()->json($response, Response::HTTP_OK);
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
