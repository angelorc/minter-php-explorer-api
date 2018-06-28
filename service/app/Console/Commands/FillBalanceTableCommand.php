<?php

namespace App\Console\Commands;

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
        $start = new \DateTime();

        Balance::truncate();

        $count = 0;

        $txFrom = DB::table('transactions')
            ->select('from')
            ->distinct()
            ->pluck('from');

        $txTo = DB::table('transactions')
            ->select('to')
            ->distinct()
            ->pluck('to');

        $addresses = array_unique(array_merge($txTo->toArray(), $txFrom->toArray()));

        foreach ($addresses as $address) {

            if (!$address) {
                continue;
            }

            try {
                $res = $this->client->request('GET', 'api/balance/' . ucfirst($address));

                $data = json_decode($res->getBody()->getContents(), 1);

                foreach ($data['result'] as $k => $v) {

                    Balance::updateOrCreate(
                        ['address' => ucfirst($address), 'coin' => mb_strtolower($k)],
                        ['amount' => $v]
                    );

                }

                $count++;
            } catch (GuzzleException $e) {
                $this->error('Error: ' . $e->getMessage());

            }
        }

        $end = new \DateTime();

        $this->info("Handled addresses : {$count} in " . ($end->getTimestamp() - $start->getTimestamp()) . 's');
    }
}