<?php

namespace App\Console\Commands;

use App\Models\Balance;
use Faker\Provider\DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Wrench\Client;
use Wrench\Exception\FrameException;
use Wrench\Exception\HandshakeException;
use Wrench\Exception\SocketException;

class AddressBalanceClientCommand extends Command
{

    protected const ENDPOINT =  '/api/balanceWS';

    /**
     * @var string
     */
    protected $signature = 'balance:listen';

    /**
     * @var string
     */
    protected $description = 'Listen balance data form Minter API';

    public function handle(): void
    {
        try {

            $client = new Client('ws://' . env('MINTER_API') . $this::ENDPOINT,
                'http://' . env('MINTER_API') .  $this::ENDPOINT);

            $client->connect();

            $response = true;

            while ($response) {

                $response = $client->receive();

                $data = null;

                foreach ($response as $r) {

                    $data = \GuzzleHttp\json_decode($r->getPayload());

                    Balance::updateOrCreate(
                        ['address' => ucfirst($data->address), 'coin' => mb_strtolower($data->coin)],
                        ['amount' => $data->balance]
                    );

                }

                // при подключении первым приходит пустой массив, поэтому проверяем
                if (!\count($response) || !isset($data->disconect)) {
                    $response = true;
                }

            }

            $client->disconnect();

        } catch (HandshakeException $exception) {
        } catch (SocketException $exception) {
        } catch (FrameException $exception) {
            Log::channel('balance')->error(
                $exception->getFile() . ' ' .
                $exception->getLine() . ': ' .
                $exception->getMessage()
            );
        }
    }
}