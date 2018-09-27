<?php

namespace App\Helpers;


use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;

class NodeExceptionHelper
{
    /**
     * @param ServerException $e
     * @return array
     */
    public static function handleServerException(ServerException $e): array
    {
        $error = json_decode($e->getResponse()->getBody(true)->getContents(), 1);

        if ($error['code'] === 107) {
            $pattern = '/.*Wanted *(\d+) (\w+)/';
            $error['log'] = preg_replace_callback($pattern, function ($matches) use (&$error) {
                $error['coin'] = $matches[2];
                $error['value'] = MathHelper::makeAmountFromIntString($matches[1]);
                $val = round(MathHelper::makeAmountFromIntString($matches[1]), 4);
                return str_replace($matches[1], $val, $matches[0]);
            }, $error['log']);
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