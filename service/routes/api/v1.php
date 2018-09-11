<?php

$router->get('/', function ()    {
    return ['data' => 'api v1'];
});

$router->get('status', StatusController::class. '@status');
$router->get('txCountChartData', StatusController::class . '@txCountChartData'); //Для совместимости со старыми версиями
$router->get('tx-count-chart-data', StatusController::class . '@txCountChartData');
$router->get('status_page', StatusController::class . '@statusPage'); //Для совместимости со старыми версиями
$router->get('status-page', StatusController::class . '@statusPage');
$router->get('get-actual-node', StatusController::class . '@getActualNode');

$router->get('blocks', BlockController::class. '@getList');
$router->get('block/{height}', BlockController::class. '@getBlockByHeight');

$router->get('transactions', TransactionController::class. '@getList');
$router->get('transaction/{hash}', TransactionController::class. '@getTransactionByHash');

$router->get('address/get-balance-channel', AddressController::class . '@getBalanceWsChannel');
$router->get('address/{address}', AddressController::class. '@address');
$router->get('address', AddressController::class. '@addresses');

$router->get('coins', CoinController::class . '@getList');

$router->get('settings/get-ws-data', SettingsController::class . '@getWsConnectData');