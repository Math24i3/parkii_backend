<?php

namespace App\Services;

use App\Models\Restriction;
use App\Models\RestrictionContract;
use App\Repositories\RestrictionRepository;
use Carbon\Carbon;
use JetBrains\PhpStorm\ArrayShape;

/**
 *
 */
class RestrictionService
{
    protected RestrictionRepository $restrictionRepository;

    /**
     * @param RestrictionRepository $restrictionRepository
     */
    public function __construct(RestrictionRepository $restrictionRepository)
    {
        $this->restrictionRepository = $restrictionRepository;
    }

    public function restrictionsByIds(array $restrictionIds): ?array
    {
        $restrictions = $this->restrictionRepository->allById($restrictionIds);
        if (!$restrictions) {
            return null;
        }
        $response = [];
        foreach ($restrictions as $restriction) {
            $response[] = $this->determineRestriction($restriction);
        }
        return $response;

    }

    /**
     * Determine a restriction output based on the restriction given
     * @param Restriction $restriction
     * @return array
     */
    public function determineRestriction(Restriction $restriction): array
    {
        $format = [
            'rule' => null,
            'parking_allowed' => 'yes',
            'ticket_required' => false,
            'limit' => null
        ];
        switch ($restriction->p_ordning) {
            case null:
                break;
            case RestrictionContract::THREE_HOUR_RESTRICTION:
                $format["rule"] = "Parking is restricted to 3 hours";
                //$response['keywords'] = preg_split("~(?:\d+|\D+)\K~", $restriction["restriktionstekst"],0, PREG_SPLIT_NO_EMPTY);
                if($restriction->restriktionstype === "Tidsbegrænsning" && $this->match('[0-9]+(?:\,|\-)[0-9]+', $restriction["restriktionstekst"])) {
                    $format["rule"] .= " between " . $restriction["restriktionstekst"];
                }

                $format["limit"] = $this->limitedParking(3);
                break;
            case RestrictionContract::VISITING_PARKING_PRIVATE:
            case RestrictionContract::VISITING_PARKING:
                $format["rule"] = "Visiting parking";
                if ($restriction->restriktion === 'ja') {
                    $format["ticket_required"] = 'yes';
                }
                break;
            case RestrictionContract::EMBASSY_PARKING:
                //TODO if car is of type ambasade then yes
                $format["rule"] = "Embassy parking";
                $format["parking_allowed"] = "criteria";
                break;
            case RestrictionContract::YELLOW_ZONE:
            case RestrictionContract::RED_ZONE:
            case RestrictionContract::GREEN_ZONE:
            case RestrictionContract::BLUE_ZONE:
                $format["rule"] = "Payment zone";
                if (self::isItSunday()) {
                    $format['ticket_required'] = false;
                } else {
                    $format['ticket_required'] = true;
                }
                $format["parking_allowed"] = "yes";
                break;
            case RestrictionContract::SHARED_CAR_PARKING:
                $format["rule"] = "Shared car parking";
                //TODO if car is of type delebil then yes
                $format["parking_allowed"] = "criteria";
                break;
            case RestrictionContract::ELECTRIC_CAR_PARKING:
                $format["rule"] = "Electric car parking";
                //TODO if car is of type electric car then yes
                $format["parking_allowed"] = "criteria";
                break;
            case RestrictionContract::DISABLED_PERSON_PARKING:
            case RestrictionContract::DISABLED_PERSON_PARKING_PRIVATE:
                $format["rule"] = "Disabled person parking";
                //TODO if car is of type disabled person then yes
                $format["parking_allowed"] = "criteria";
                break;
            case RestrictionContract::MOTORBIKE_PARKING:
                $format["rule"] = "Motorbike parking";
                if (self::isItSunday()) {
                    $format['ticket_required'] = false;
                } else {
                    $format['ticket_required'] = true;
                }
                $format["parking_allowed"] = "yes";
                break;
            case RestrictionContract::REGULATED_PARKING:
            case RestrictionContract::REGULATED_PARKING_PRIVATE:
                $format["rule"] = "Regulated private parking";
                $format["parking_allowed"] = "no";
                break;
            case RestrictionContract::PRIVATE_PARKING_SHARED:
            case RestrictionContract::PRIVATE_PARKING_DIV:
            case RestrictionContract::PRIVATE_PARKING:
            case RestrictionContract::PRIVATE_PARKING_2:
                $format["rule"] = "Private parking";
                $format["parking_allowed"] = "condition";
                break;
            case RestrictionContract::TAXI_PARKING:
                $format["rule"] = "Taxi parking";
                //TODO check if car is taxi
                $format["parking_allowed"] = "no";
                break;
            case RestrictionContract::TURIST_BUS_PARKING:
                $format["rule"] = "Turist-bus parking";
                $format["parking_allowed"] = "no";
                break;
        }
        return array_merge($restriction->toArray(), $format);

    }

    /**
     * @param int $time
     * @return array
     */
    protected function limitedParking(int $time) : array {
        return [
            'now' => Carbon::now()->format('H:i'),
            'end' => Carbon::now()->addHours($time)->format('H:i')
        ];
    }

    /**
     * @return bool
     */
    public static function isItSunday() {

        return Carbon::now()->dayOfWeek === 0;
    }

    /**
     * @param string $regex
     * @param string $text
     * @return bool
     */
    private function match(string $regex, string $text): bool
    {
        if (preg_match("/$regex/", $text,$match)){
            return true;
        }
        return false;
    }

    /**
     * Returns the distance to and from in kilometers
     * @param array $from
     * @param array $to
     * @return float
     */
    public static function calculateDistance(array $from, array $to): float
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
