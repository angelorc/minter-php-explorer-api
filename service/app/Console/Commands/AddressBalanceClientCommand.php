<?php

namespace App\Console\Commands;

use App\Models\Balance;
use App\Models\BalanceChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Wrench\Client;
use Wrench\Exception\FrameException;
use Wrench\Exception\HandshakeException;
use Wrench\Exception\SocketException;

class AddressBalanceClientCommand extends Command
{

    protected const ENDPOINT = '/api/balanceWS';

    /**
     * @var string
     */
    protected $signature = 'balance:listen';

    /**
     * @var string
     */
    protected $description = 'Listen balance data form Minter API';

    /**
     * @var \phpcent\Client
     */
    protected $centrifuge;


    /**
     * AddressBalanceClientCommand constructor.
     * @param \phpcent\Client $centrifuge
     */
    public function __construct(\phpcent\Client $centrifuge)
    {
        parent::__construct();

        $this->centrifuge = $centrifuge;
    }


    public function handle(): void
    {
        try {

            $client = new Client('ws://' . env('MINTER_API') . $this::ENDPOINT,
                'http://' . env('MINTER_API') . $this::ENDPOINT);

            $client->connect();

            $response = true;

            while ($response) {

                $response = $client->receive();

                $data = null;

                $channels = [];

                foreach ($response as $r) {

                    $data = \GuzzleHttp\json_decode($r->getPayload());

                    $balance = Balance::updateOrCreate(
                        ['address' => mb_strtolower($data->address), 'coin' => mb_strtoupper($data->coin)],
                        ['amount' => $data->balance]
                    );

                    /** @var Collection $bl */
                    $balanceChannelList = BalanceChannel::where('address', mb_strtolower($data->address))->get();

                    foreach ($balanceChannelList as $balanceChannel) {
                        /** BalanceChannel $balanceChannel */
                        $channels[$balanceChannel->name][] = $balance;
                    }

                }

                if (\count($channels)) {
                    foreach ($channels as $name => $balances) {
                        foreach ($balances as $balance) {
                            $this->centrifuge->publish($name, [
                                'address' => mb_strtolower($balance->address),
                                'coin' => mb_strtoupper($balance->coin),
                                'amount' => $balance->amount
                            ]);
                        }

                    }
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