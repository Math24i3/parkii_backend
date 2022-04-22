<?php

namespace App\Services;

/**
 * Interface CdnService
 */
interface CdnService
{
    /**
     * @param $fileName
     * @return mixed
     */
    public function purge($fileName);
}
