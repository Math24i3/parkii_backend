<?php

namespace Tests\Feature;

use Carbon\Carbon;
use App\Services\RestrictionService;
use Tests\TestCase;

class RestrictionTest extends TestCase
{

    /**
     * Testing if calculating distance is working as expected
     *
     * @return void
     */
    public function test_calculate_distance_between_two_latitude_and_longitude()
    {
        $distance = RestrictionService::calculateDistance(
            [
                'latitude' => "55.697910",
                'longitude' => "12.530559"
            ],
            [
                'longitude' => "12.538142",
                'latitude' => "55.699894"
            ]
        );
        $this->assertEquals(0.52386311537723, $distance);
    }

    public function test_is_it_sunday()
    {
        $is_it_sunday = RestrictionService::isItSunday();
        if(Carbon::now()->dayOfWeek === 0) {
            $this->assertEquals(true, $is_it_sunday);
        } else {
            $this->assertEquals(false, $is_it_sunday);
        }
    }
}
