<?php

namespace App\Helpers;


class DateTimeHelper
{
    /**
     * Parse DateTime from Minter Node DateTime Format
     * @param string $stringDateTime
     * @return \DateTime|null
     */
    public static function parse(string $stringDateTime): ?\DateTime
    {
        $result = null;
        $format = false;
        preg_match_all('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{1,9}[\+\-]\d{2}:\d{2}/', $stringDateTime, $result);
        if (count($result[0])) {
            $format = 'Y-m-d H:i:s.uP';
        };

        preg_match_all('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{1,9}Z/', $stringDateTime, $result);
        if (count($result[0])) {
            $format = 'Y-m-d H:i:s.uZ';
        }

        if ($format) {
            $dt = [];
            preg_match('/(.*)T(.*)\.(\d{1,9})(.*)/', $stringDateTime, $dt);
            $str = $dt[1] . ' ' . $dt[2] . '.' . substr($dt[3], 0, 6) . $dt[4];
            return date_create_from_format($format, $str);
        }

        return null;
    }

    public static function getDateTimeAsFloat(string $stringSateTime): string
    {
        $ns = preg_replace('/(.*)\.(.*)Z/', '$2', $stringSateTime);

        if (!$ns) {
            $result = \Carbon\Carbon::now();
        } else {
            $result = str_replace(".{$ns}Z", '', $stringSateTime);
        }

        $result = new \Carbon\Carbon($result);

        return $result->timestamp . '.' . $ns;
    }

}