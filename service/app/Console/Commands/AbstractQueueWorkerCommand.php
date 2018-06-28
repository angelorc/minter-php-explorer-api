<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Базовый класс для консольных обработчиков очередей
 */
abstract class AbstractQueueWorkerCommand extends Command
{
    /**
     * @return string
     */
    public function getHandlerId(): string
    {
        return $this->argument('num');
    }

    /**
     * @return string
     */
    public function getWaitTimeout(): string
    {
        return $this->option('timeout');
    }

    /**
     * @param AMQPMessage $msg
     */
    final public function handleQueue(AMQPMessage $msg): void
    {
        try {
            $this->handleMessage($msg);
            $this->ack($msg);
        } catch (Exception $e) {
            $this->ack($msg); // Всегда удаляем сообщение из очереди, чтобы позволить обработаться другим
        }

        if ($this->stepMode()) {
            exit(0);
        }
    }

    /**
     * @param AMQPMessage $message
     *
     * @return bool
     */
    abstract protected function handleMessage(AMQPMessage $message): bool;

    /**
     * @param AMQPMessage $msg
     */
    final protected function ack(AMQPMessage $msg): void
    {
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }

    /**
     * once|pipeline
     */
    protected function stepMode(): bool
    {
        return $this->argument('mode') === 'once';
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return [
            [
                'num',
                InputArgument::REQUIRED,
                'ID запущенного обработчика',
            ],
            [
                'mode',
                InputArgument::OPTIONAL,
                'Режим обработки очереди',
            ],
        ];
    }

    public function getOptions(): array
    {
        return [
            [
                'timeout',
                't',
                InputOption::VALUE_REQUIRED,
                'Ожидать новых сообщений из очереди указанное количество секунд, если новых не будет - выйти. По умолчанию - бесконечное ожидание',
                0,
            ],
        ];
    }

    /**
     * Определяет имя очереди, из которой команда будет читать и обрабатывать сообщения
     *
     * @return string
     */
    abstract protected function getQueue(): string;
}
