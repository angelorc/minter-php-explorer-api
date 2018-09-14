<?php

namespace App\Services;


interface MinterServiceInterface
{
    /**
     * Store date from node to DB
     * @param int $blockHeight
     */
    public function storeNodeData(int $blockHeight): void;
}