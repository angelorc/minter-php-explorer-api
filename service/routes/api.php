<?php


$router->group([/*'middleware' => 'api_response', */ 'prefix' => 'api'], function () use ($router) {

    /**
     * @SWG\Info(title="Minter Explorer API", version="1.0")
     */
    $router->group(['prefix' => 'v1'], function () use ($router) {

        require __DIR__ . '/../routes/api/v1.php';

    });

});
