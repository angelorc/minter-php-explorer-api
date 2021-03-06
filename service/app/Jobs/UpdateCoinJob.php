<?php

namespace App\Jobs;

use App\Helpers\LogHelper;
use App\Models\Coin;
use App\Services\MinterApiService;
use App\Traits\NodeTrait;
use GuzzleHttp\Exception\GuzzleException;

class UpdateCoinJob extends Job
{
    use NodeTrait;

    /** @var array */
    protected $coins;

    public $queue = 'main';

    /**
     * Create a new job instance.
     *
     * @param array $coins List of coins for update
     */
    public function __construct(array $coins)
    {
        $this->coins = $coins;
    }

    /**
     * Execute the job.
     *a
     * @return void
     */
    public function handle(): void
    {
        $apiService = new MinterApiService($this->getActualNode());
        try {

            foreach ($this->coins as $c) {

                if ($c === env('MINTER_BASE_COIN')) {
                    continue;
                }

                $data = $apiService->getCoinInfo($c);
                /** @var Coin $coin */
                $coin = Coin::where('symbol', '=', mb_strtoupper($c))->first();
                $coin->volume = $data['volume'];
                $coin->crr = $data['crr'];
                $coin->reserve_balance = $data['reserve_balance'];
                $coin->save();
            }

        } catch (GuzzleException $exception) {
            LogHelper::apiError($exception);
        } catch (\Exception $exception) {
            LogHelper::error($exception);
        }
    }

}
