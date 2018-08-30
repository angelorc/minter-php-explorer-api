<?php

namespace App\Services;


use App\Helpers\StringHelper;
use App\Models\Balance;
use App\Models\BalanceChannel;
use App\Models\Coin;
use App\Repository\BalanceRepositoryInterface;
use Illuminate\Support\Collection;
use GuzzleHttp\Client as GuzzleHttpClient;

class BalanceService implements BalanceServiceInterface
{
    /**
     * @var BalanceRepositoryInterface
     */
    protected $balanceRepository;

    /**
     * @var GuzzleHttpClient
     */
    protected $httpClient;

    /**
     * @var \phpcent\Client
     */
    protected $centrifuge;

    /**
     * BalanceService constructor.
     * @param BalanceRepositoryInterface $balanceRepository
     * @param \phpcent\Client $centrifuge
     * @param GuzzleHttpClient $httpClient
     */
    public function __construct(
        BalanceRepositoryInterface $balanceRepository,
        \phpcent\Client $centrifuge,
        GuzzleHttpClient $httpClient
    ) {
        $this->balanceRepository = $balanceRepository;
        $this->centrifuge = $centrifuge;
        $this->httpClient = $httpClient;
    }

    /**
     * Получить баланс адреса
     * @param string $address
     * @return Collection
     */
    public function getAddressBalance(string $address): Collection
    {
        $result = $this->balanceRepository->getBalanceByAddress($address)->map(function ($item) {

            $coin = new Coin($item->coin, $item->amount);

            return [
                'coin' => $coin->getName(),
                'amount' => $coin->getAmount(),
                'baseCoinAmount' => $coin->getAmount(),
                'usdAmount' => $coin->getUsdAmount(),
            ];

        });

        return $result;
    }

    /**
     * Обновить баланс адреса данными из ноды
     * @param string $address
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateAddressBalanceFromNodeAPI(string $address): void
    {
        $res = $this->httpClient->request('GET', 'api/balance/' . StringHelper::mb_ucfirst($address));
        $data = json_decode($res->getBody()->getContents(), 1);

        Balance::where('address', 'ilike', $address)->delete();

        foreach ($data['result']['balance'] as $coin => $value) {
            $balance = new Balance;
            $balance->address = mb_strtolower($address);
            $balance->coin = mb_strtoupper($coin);
            $balance->amount = $value;
            $balance->save();
        }
    }

    /**
     * @param string $address
     */
    public function broadcastNewBalances(string $address): void
    {
        $channels = [];
        $balances = null;

        /** @var Collection $bl */
        $balanceChannelList = BalanceChannel::where('address', 'ilike', $address)->get();

        if (\count($balanceChannelList)) {
            $balance = Balance::where('address', 'ilike', $address)->get();

            foreach ($balanceChannelList as $balanceChannel) {
                /** BalanceChannel $balanceChannel */
                $channels[$balanceChannel->name] = $balance;
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

    }
}