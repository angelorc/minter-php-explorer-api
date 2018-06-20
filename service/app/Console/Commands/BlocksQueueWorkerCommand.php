<?php

namespace App\Console\Commands;

use App\Helpers\RmqHelper;
use App\Services\BlockServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class BlocksQueueWorkerCommand extends AbstractQueueWorkerCommand
{
    public const QUEUE_NAME = 'explorer:blocks';

    /**
     * @var string
     */
    protected $name = 'rmq:worker:explorer_blocks';

    protected $description = 'RabbitMQ worker for blocks save';

    /** @var BlockServiceInterface */
    private $blockService;

    /** @var RmqHelper */
    private $rmqHelper;

    /**
     * PullBlockDataCommand constructor.
     * @param BlockServiceInterface $blockService
     * @param RmqHelper $rmqHelper
     */
    public function __construct(BlockServiceInterface $blockService, RmqHelper $rmqHelper)
    {
        parent::__construct();

        $this->blockService = $blockService;

        $this->rmqHelper = $rmqHelper;
    }


    public function handle(): void
    {
        Log::notice(class_basename(static::class) . ': Запущен обработчик очереди');

        $this->rmqHelper->loopConsume(
            [$this, 'handleQueue'],
            $this->getQueue(),
            $this->getHandlerId(),
            $this->getWaitTimeout()
        );
    }

    /**
     * Определяет имя очереди, из которой команда будет читать и обрабатывать сообщения
     *
     * @return string
     */
    protected function getQueue(): string
    {
        return self::QUEUE_NAME;
    }

    /**
     * @param AMQPMessage $message
     *
     * @return bool
     */
    protected function handleMessage(AMQPMessage $message): bool
    {
        $data = \GuzzleHttp\json_decode($message->getBody(), true);

        try {
            if (isset($data['blockHeight'])) {
                $blockData = $this->blockService->pullBlockData($data['blockHeight']);
                $this->blockService->saveFromApiData($blockData);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
        } catch (GuzzleException $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
        }

        return true;
    }
}