<?php

namespace App\Models;

interface RestrictionContract
{

    public const THREE_HOUR_RESTRICTION = "3 timers restriktion";
    public const VISITING_PARKING = "Besøgsplads";
    public const VISITING_PARKING_PRIVATE = "Besøgsplads, privat ordning, privat fællesvej";
    public const EMBASSY_PARKING = "Ambassade parkering";
    public const YELLOW_ZONE = "Gul betalingszone";
    public const BLUE_ZONE = "Blå betalingszone";
    public const GREEN_ZONE = "Grøn betalingszone";
    public const RED_ZONE = "Rød betalingszone";
    public const SHARED_CAR_PARKING = "Delebil parkering";
    public const ELECTRIC_CAR_PARKING = "El-Bil plads";
    public const DISABLED_PERSON_PARKING = "Handicap parkering";
    public const DISABLED_PERSON_PARKING_PRIVATE = "Handicap parkering, privat ordning, privat fællesvej";
    public const MOTORBIKE_PARKING = "Motorcykel parkering";
    public const REGULATED_PARKING = '"Off. reguleret';
    public const REGULATED_PARKING_PRIVATE = "Off. reguleret, privat grund";
    public const PRIVATE_PARKING_SHARED = "Privat ordning, privat fællesvej";
    public const PRIVATE_PARKING_DIV = "Privat ordning, diverse";
    public const PRIVATE_PARKING = "Privat ordning";
    public const PRIVATE_PARKING_2 = '"Privat ordning';
    public const TAXI_PARKING = "Taxiholdeplads";
    public const TURIST_BUS_PARKING =  "Turistbus plads";




}
