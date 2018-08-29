<?php

namespace App\Helpers;


class StringHelper
{
    public static function mb_ucfirst(string $string, string $encoding = 'UTF8'): string
    {
        $strLen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strLen - 1, $encoding);

        return mb_strtoupper($firstChar, $encoding) . $then;
    }
}