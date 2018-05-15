<?php

namespace App\Providers;

use App\Repository\BlockRepository;
use App\Repository\BlockRepositoryInterface;
use App\Repository\TransactionRepository;
use App\Repository\TransactionRepositoryInterface;
use App\Services\BlockService;
use App\Services\BlockServiceInterface;
use App\Services\StatusService;
use App\Services\StatusServiceInterface;
use App\Services\TransactionService;
use App\Services\TransactionServiceInterface;
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
        $this->app->singleton(BlockRepositoryInterface::class, BlockRepository::class);
        $this->app->singleton(BlockServiceInterface::class, BlockService::class);
        $this->app->singleton(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->singleton(TransactionServiceInterface::class, TransactionService::class);
        $this->app->singleton(StatusServiceInterface::class, StatusService::class);
    }
}