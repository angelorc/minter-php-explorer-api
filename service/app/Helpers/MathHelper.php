<?php

namespace App\Helpers;

use App\Models\Coin;

/**
 * Class MathHelper
 * @package App\Helpers
 */
class MathHelper
{

    public const DEFAULT_SCALE = 18;
    public const RESERVE       = 2;

    /**
     * @param string $intStrValue
     * @param int    $scale
     *
     * @return string
     */
    public static function makeAmountFromIntString(string $intStrValue, $scale = self::DEFAULT_SCALE): string
    {
        return bcmul($intStrValue, Coin::PIP_STR, $scale);
    }

    /**
     * @param string $intStrValue
     * @param int $scale
     *
     * @return string
     */
    public static function makeCommissionFromIntString(string $intStrValue, $scale = self::DEFAULT_SCALE): string
    {
        return bcmul($intStrValue, Coin::UNIT_STR, $scale);
    }
}