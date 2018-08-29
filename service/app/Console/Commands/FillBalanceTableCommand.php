<?php

namespace App\Console\Commands;

use App\Helpers\StringHelper;
use App\Models\Balance;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class FillBalanceTableCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'balance:update_data';

    /**
     * @var string
     */
    protected $description = 'Fill balance table';

    /**
     * @var Client
     */
    protected $client;


    /**
     * FillBalanceTableCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = new Client(['base_uri' => 'http://' . env('MINTER_API')]);

    }

    /**
     *
     * @throws \RuntimeException
     */
    public function handle(): void
    {
        $start = $this->microtime_float();
        $count = 0;

        $addresses = DB::select('
            select  distinct address from (
              select  "from"  as address from transactions
              union
              select  "to"  as address from transactions
            ) as list where address notnull;
        ');

        $addressesList = [];

        foreach ($addresses as $a) {
            $address = $a->address;
            try {
                $res = $this->client->request('GET', 'api/balance/' . StringHelper::mb_ucfirst($address));
                $data = json_decode($res->getBody()->getContents(), 1);
                foreach ($data['result']['balance'] as $k => $v) {
                    $balance = new Balance();
                    $balance->address = mb_strtolower($address);
                    $balance->coin = mb_strtoupper($k);
                    $balance->amount = $v;
                    $addressesList[] = $balance;
                }
                $count++;
            } catch (GuzzleException $e) {
                $this->error('Error: ' . $e->getMessage());

            }
        }

        Balance::truncate();
        foreach ($addressesList as $address) {
            DB::transaction(function () use ($address) {
                $address->save();
            });
        }
        $end = $this->microtime_float();
        $this->info("Balances have been update. Handled addresses: {$count} in " . ($end - $start) . 's');
    }

    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}