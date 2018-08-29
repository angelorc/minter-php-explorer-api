<?php

namespace App\Console\Commands;

use App\Helpers\StringHelper;
use App\Models\Balance;
use App\Models\BalanceChannel;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Wrench\Client as wsClient;
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
     * @var Client
     */
    protected $httpClient;

    /**
     * AddressBalanceClientCommand constructor.
     *
     * @param \phpcent\Client $centrifuge
     * @param Client $httpClient
     */
    public function __construct(\phpcent\Client $centrifuge, Client $httpClient)
    {
        parent::__construct();
        $this->centrifuge = $centrifuge;
        $this->httpClient = $httpClient;
    }


    public function handle(): void
    {
        try {

            $client = new wsClient('ws://' . env('MINTER_API') . $this::ENDPOINT,
                'http://' . env('MINTER_API') . $this::ENDPOINT);
            $client->connect();
            $response = true;

            while ($response) {
                $response = $client->receive();
                // if $response === null - client is not connected
                if (is_null($response)) {
                    break;
                }

                $data = null;
                $addresses = [];

                foreach ($response as $r) {
                    $data = \GuzzleHttp\json_decode($r->getPayload());
                    $addresses[] = mb_strtolower($data->address);
                }

                $addresses = array_unique($addresses);
                if (\count($addresses)) {
                    $this->updateAddressesBalance($addresses);
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

    private function updateAddressesBalance(array $addresses)
    {
        $channels = [];

        foreach ($addresses as $address) {
            $res = $this->httpClient->request('GET', 'api/balance/' . StringHelper::mb_ucfirst($address));
            $data = json_decode($res->getBody()->getContents(), 1);

            Balance::where('address', 'ilike', $address)->delete();

            /** @var Collection $bl */
            $balanceChannelList = BalanceChannel::where('address', 'ilike', $address)->get();

            foreach ($data["result"]["balance"] as $coin => $value) {
                $balance = new Balance;
                $balance->address = mb_strtolower($address);
                $balance->coin = mb_strtoupper($coin);
                $balance->amount = $value;
                $balance->save();

                foreach ($balanceChannelList as $balanceChannel) {
                    /** BalanceChannel $balanceChannel */
                    $channels[$balanceChannel->name][] = $balance;
                }
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

        $this->clearOldChannels();
    }

    private function clearOldChannels()
    {
        $dt = new \DateTime();
        $dt->modify('-10 day');
        BalanceChannel::whereDate('created_at', '<=', $dt->format('Y-m-d H:i:s'));
    }
}