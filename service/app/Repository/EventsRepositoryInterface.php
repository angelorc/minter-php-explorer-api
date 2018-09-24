<?php

namespace App\Repository;


interface EventsRepositoryInterface extends ModelRepositoryInterface
{
    /**
     * @param string $address
     * @param string $scale
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @return array
     */
    public function getChartData(string $address, string $scale, \DateTime $startTime, \DateTime $endTime): array;
}