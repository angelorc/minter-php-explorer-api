<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*** @var Response $response */
        $response = $next($request);
        $original = $response->getOriginalContent();
        $status = $response->status();
        $rawStatus = http_response_code();
        $status = ($status == 200 && ($status != $rawStatus && $rawStatus > 1)) ? $rawStatus : $status;
        $content = [
            'success' => $response->isSuccessful(),
            'code' => $status,
            'data' => $original
        ];
        $content['message'] = (is_array($original) && isset($original['message'])) ? $original['message'] : '';

        return new JsonResponse($content, $status, $response->headers->all());
    }
}
