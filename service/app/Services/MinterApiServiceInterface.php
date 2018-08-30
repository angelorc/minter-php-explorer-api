<?php

namespace App\Services;


interface MinterApiServiceInterface
{

    /**
     * Get node status data
     * @return array
     */
    public function getNodeStatusData(): array;
}