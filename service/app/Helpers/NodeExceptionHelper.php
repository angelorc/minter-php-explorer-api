<?php

namespace App\Helpers;


use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;

class NodeExceptionHelper
{
    /**
     * @param BadResponseException $e
     * @return array
     */
    public static function handleNodeException(BadResponseException $e): array
    {
        $error = json_decode($e->getResponse()->getBody(true)->getContents(), 1);

        switch ($error['code']) {
            case 1:
            case 103:
                $pattern = '/.*has\D+(\d+).*required (\d+)/i';
                $error['log'] = preg_replace_callback($pattern, function ($matches) use (&$error) {
                    $error['has'] = MathHelper::makeAmountFromIntString($matches[1]);
                    $error['required'] = MathHelper::makeAmountFromIntString($matches[2]);
                    $log = strtr($matches[0], [$matches[1] => $error['has'], $matches[2] => $error['required']]);
                    return $log;
                }, $error['log']);
                break;
            case 107:
                $pattern = '/.*Wanted *(\d+) (\w+)/';
                $error['log'] = preg_replace_callback($pattern, function ($matches) use (&$error) {
                    $error['coin'] = $matches[2];
                    $error['value'] = MathHelper::makeAmountFromIntString($matches[1]);
                    $val = round(MathHelper::makeAmountFromIntString($matches[1]), 4);
                    return str_replace($matches[1], $val, $matches[0]);
                }, $error['log']);
                break;
            default:
                $error['log'] = 'Unknown error';
                $error['code'] = 0;
                break;
        }

        return $error;
    }

    /**
     * @param GuzzleException $e
     * @return array
     */
    public static function handleGuzzleException(GuzzleException $e): array
    {
        return [
            'code' => 1,
            'log' => $e->getMessage(),
        ];
    }
}