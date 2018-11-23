<?php

namespace App\Providers;

use App\Models\Reward;
use App\Models\Slash;
use App\Models\Validator;
use App\Repository\BalanceRepository;
use App\Repository\BalanceRepositoryInterface;
use App\Repository\BlockRepository;
use App\Repository\BlockRepositoryInterface;
use App\Repository\CoinsRepository;
use App\Repository\CoinsRepositoryInterface;
use App\Repository\RewardsRepository;
use App\Repository\RewardsRepositoryInterface;
use App\Repository\SlashesRepository;
use App\Repository\SlashesRepositoryInterface;
use App\Repository\TransactionRepository;
use App\Repository\TransactionRepositoryInterface;
use App\Repository\ValidatorRepository;
use App\Repository\ValidatorRepositoryInterface;
use App\Services\BalanceService;
use App\Services\BalanceServiceInterface;
use App\Services\BlockService;
use App\Services\BlockServiceInterface;
use App\Services\CoinService;
use App\Services\CoinServiceInterface;
use App\Services\StatusService;
use App\Services\StatusServiceInterface;
use App\Services\TransactionService;
use App\Services\TransactionServiceInterface;
use App\Services\ValidatorService;
use App\Services\ValidatorServiceInterface;
use Illuminate\Support\ServiceProvider;


/**
 * Регистрация IoC биндингов в контейнере
 */
class ContainerServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {

        /** Repositories */
        $this->app->singleton(BalanceRepositoryInterface::class, BalanceRepository::class);
        $this->app->singleton(BlockRepositoryInterface::class, BlockRepository::class);
        $this->app->singleton(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->singleton(CoinsRepositoryInterface::class, CoinsRepository::class);
        $this->app->singleton(RewardsRepositoryInterface::class, function () {
            return new RewardsRepository(new Reward());
        });
        $this->app->singleton(SlashesRepositoryInterface::class, function () {
            return new SlashesRepository(new Slash());
        });
        $this->app->singleton(ValidatorRepositoryInterface::class, function () {
            return new ValidatorRepository(new Validator());
        });

        /** Services */
        $this->app->singleton(BalanceServiceInterface::class, BalanceService::class);
        $this->app->singleton(BlockServiceInterface::class, BlockService::class);
        $this->app->singleton(StatusServiceInterface::class, StatusService::class);
        $this->app->singleton(TransactionServiceInterface::class, TransactionService::class);
        $this->app->singleton(ValidatorServiceInterface::class, ValidatorService::class);
        $this->app->singleton(CoinServiceInterface::class, CoinService::class);
        $this->app->singleton(ValidatorServiceInterface::class, ValidatorService::class);

        $this->app->singleton(\phpcent\Client::class, function () {
            $centrifuge = new \phpcent\Client(env('CENTRIFUGE_URL', 'http://localhost:8000'));
            $centrifuge->setSecret(env('CENTRIFUGE_SECRET', null));
            return $centrifuge;
        });

    }
}