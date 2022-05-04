<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{
    use HasFactory;

    public $timestamps = FALSE;
    public $incrementing = false;

    protected $fillable = [
        'vejkode',
        'vejnavn',
        'antal_pladser',
        'restriktion',
        'vejstatus',
        'vejside',
        'bydel',
        'p_ordning',
        'p_type',
        'p_status',
        'rettelsedato',
        'oprettelsesdato',
        'bemaerkning',
        'id',
        'restriktionstype',
        'restriktionstekst',
    ];
}
