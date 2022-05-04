<?php

namespace App\Services;

use Carbon\Carbon;
use JetBrains\PhpStorm\ArrayShape;

/**
 *
 */
class RestrictionService
{

    /**
     * @return void
     */
    public function determineRestriction() {

    }

    /**
     * @param int $time
     * @return array
     */
    #[ArrayShape(['now' => Carbon::class, 'end' => Carbon::class])]
    public function limitedParking(int $time) : array {
        return [
            'now' => Carbon::now()->format('H:i'),
            'end' => Carbon::now()->addHours($time)->format('H:i')
        ];
    }

}
