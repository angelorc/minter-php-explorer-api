<?php

$router->get('/', function ()    {
    return ['data' => 'api v1'];
});

$router->get('status', StatusController::class. '@status');
$router->get('txCountChartData', StatusController::class . '@txCountChartData'); //Для совместимости со старыми версиями
$router->get('tx-count-chart-data', StatusController::class . '@txCountChartData');
$router->get('status_page', StatusController::class . '@statusPage'); //Для совместимости со старыми версиями
$router->get('status-page', StatusController::class . '@statusPage');
$router->get('get-actual-node', StatusController::class . '@getActualNodeData');

$router->get('blocks', BlockController::class. '@getList');
$router->get('block/{height}', BlockController::class. '@getBlockByHeight');

$router->post('transaction/push', TransactionController::class . '@pushTransactionToBlockChain');
$router->get('transaction/get-count/{address}', TransactionController::class . '@getCountByAddress');
$router->get('transaction/{hash}', TransactionController::class . '@getTransactionByHash');
$router->get('transactions', TransactionController::class. '@getList');

$router->get('address/get-balance-channel', SettingsController::class . '@getBalanceWsChannel');//Для совместимости со старыми версиями
$router->get('address/{address}', AddressController::class. '@address');
$router->get('address', AddressController::class. '@addresses');

$router->get('coins', CoinController::class . '@getList');

$router->get('settings/get-balance-channel', SettingsController::class . '@getBalanceWsChannel');
$router->get('settings/get-ws-data', SettingsController::class . '@getWsConnectData');

$router->get('events/rewards/chart/{address}', EventController::class . '@getRewardsChartData');
$router->get('events/rewards', EventController::class . '@getRewardsList');
$router->get('events/slashes', EventController::class . '@getSlashesList');

$router->get('estimate/tx-commission', EstimateController::class . '@txCommission');
$router->get('estimate/coin-buy', EstimateController::class . '@buyCoin');
$router->get('estimate/coin-sell', EstimateController::class . '@sellCoin');
