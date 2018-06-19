<?php

namespace App\Console\Commands;


use App\Helpers\RmqHelper;
use Illuminate\Console\Command;

/**
 * Команда для объявления очередей RabbitMQ
 */
class DeclareQueuesCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'rmq:declare';

    /**
     * @var string
     */
    protected $description = 'Объявляет очереди и обменники';

    /** @var RmqHelper */
    private $rmqHelper;

    /**
     * DeclareQueuesCommand constructor.
     * @param RmqHelper $rmqHelper
     */
    public function __construct(RmqHelper $rmqHelper)
    {
        parent::__construct();

        $this->rmqHelper = $rmqHelper;
    }

    /**
     *
     */
    public function handle(): void
    {
        $this->declareQueues('rmq.explorer');
    }

    /**
     * @param string $configPrefix
     */
    private function declareQueues(string $configPrefix): void
    {
        $this->info('Объявляем очереди и обменники ' . $configPrefix);

        /** @var string[] $queues */
        $queues = config($configPrefix . '.queues');

        foreach ($queues as $queue) {
            $this->rmqHelper->declareExchange($queue);
            $this->info("\tОбменник: {" . $queue . '} объявлен');
            $this->rmqHelper->declareQueue($queue);
            $this->info("\tОчередь: {" . $queue . '} объявлена');
            $this->rmqHelper->bindQueue($queue, $queue, $queue);
            $this->info("\tСвязь:   {" . $queue . '}->{' . $queue . '} по роуту {' . $queue . "} объявлена\n");
        }

        $this->info('RMQ обновлен ' . $configPrefix);
    }
}
