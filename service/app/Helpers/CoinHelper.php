<?php
namespace App\Helpers;


class CoinHelper
{
    public const MNT_IN_UNIT = 0.001;

    public static function convertUnitToMnt($value)
    {
        return $value * self::MNT_IN_UNIT;
    }
}