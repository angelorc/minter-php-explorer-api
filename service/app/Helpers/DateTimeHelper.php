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


    /**
     * Parse DateTime from Minter Node DateTime Format
     * @param string $stringDateTime
     * @return \DateTime|null
     */
    public static function parse(string $stringDateTime): ?\DateTime
    {
        $result = null;
        preg_match_all('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{1,9}[\+\-]\d{2}:\d{2}/', $stringDateTime, $result);
        if(count($result[0])){
            $dt = [];
            preg_match('/(.*)T(.*)\.(\d{1,9})(.*)/', $stringDateTime, $dt);

            $str = $dt[1] . ' ' . $dt[2] . '.' .  substr($dt[3], 0, 6) . $dt[4];

           return date_create_from_format ( 'Y-m-d H:i:s.uP' , $str);
        };

        return null;
    }


}