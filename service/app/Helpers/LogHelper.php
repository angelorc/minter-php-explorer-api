<?php

namespace App\Helpers;


use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class LogHelper
{
    public static function apiError(GuzzleException $exception): void
    {
        Log::channel('api')->error(
            $exception->getFile() . ' line ' .
            $exception->getLine() . ': ' .
            $exception->getMessage()
        );
    }

    public static function transactionsError(GuzzleException $exception, int $blockHeight, string $hash): void
    {
        Log::channel('transactions')->error(
            $exception->getFile() . ' ' .
            $exception->getLine() . ': ' .
            $exception->getMessage() .
            ' Block: ' . $blockHeight .
            ' Transaction: ' . $hash
        );
    }

    public static function error(\Exception $exception): void
    {
        Log::error(
            $exception->getFile() . ' line ' .
            $exception->getLine() . ': ' .
            $exception->getMessage()
        );
    }
}