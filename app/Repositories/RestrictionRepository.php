<?php

namespace App\Repositories;

use App\Models\Restriction;

class RestrictionRepository
{

    public function allById(array $ids) {
        return Restriction::find( $ids);
    }

}
