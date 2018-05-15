<?php

$router->get('/', function ()    {
    return ['data' => 'api v1'];
});

$router->get('status', StatusController::class. '@status');
$router->get('txCountChartData', StatusController::class. '@txCountChartData');

$router->get('blocks', BlockController::class. '@getList');
$router->get('block/{height}', BlockController::class. '@getBlockByHeight');

$router->get('transactions', TransactionController::class. '@getList');
$router->get('transaction/{hash}', TransactionController::class. '@getTransactionByHash');
