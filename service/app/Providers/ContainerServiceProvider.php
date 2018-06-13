<?php

namespace App\Providers;

use App\Repository\BalanceRepository;
use App\Repository\BalanceRepositoryInterface;
use App\Repository\BlockRepository;
use App\Repository\BlockRepositoryInterface;
use App\Repository\TransactionRepository;
use App\Repository\TransactionRepositoryInterface;
use App\Services\BalanceService;
use App\Services\BalanceServiceInterface;
use App\Services\BlockService;
use App\Services\BlockServiceInterface;
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

        /** Services */
        $this->app->singleton(BalanceServiceInterface::class, BalanceService::class);
        $this->app->singleton(BlockServiceInterface::class, BlockService::class);
        $this->app->singleton(StatusServiceInterface::class, StatusService::class);
        $this->app->singleton(TransactionServiceInterface::class, TransactionService::class);
        $this->app->singleton(ValidatorServiceInterface::class, ValidatorService::class);

    }
}