<?php

namespace App\Helpers;


class DateTimeHelper
{
    /**
     * @param string $stringSateTime
     * @return \Carbon\Carbon
     */
    public static function getDateTimeFromNanoSeconds(string $stringSateTime): \Carbon\Carbon
    {
        $nano = preg_replace('/(.*)\.(.*)Z/', '$2', $stringSateTime);

        if (!$nano) {
            return \Carbon\Carbon::now();
        }

        $result = str_replace(".{$nano}Z", '.' . substr($nano, 0, 6) . 'Z', $stringSateTime);

        return new \Carbon\Carbon($result);
    }

    public static function getDateTimeAsFloat(string $stringSateTime): string
    {
        $nano = preg_replace('/(.*)\.(.*)Z/', '$2', $stringSateTime);

        if (!$nano) {
            $result = \Carbon\Carbon::now();
        } else {
            $result = str_replace(".{$nano}Z", '', $stringSateTime);
        }

        $result = new \Carbon\Carbon($result);

        return $result->timestamp . '.' . $nano;
    }


}