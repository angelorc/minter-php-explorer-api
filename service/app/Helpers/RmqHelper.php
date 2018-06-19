<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;


/**
 * Утилитарные функции для работы с очередями
 */
class RmqHelper
{
    protected const BASIC_QOS = 1;

    /** @var array */
    private $config;

    /** @var  AMQPStreamConnection $connection */
    private $connection;

    /** @var  AMQPChannel|null $channel */
    private $channel;

    /**
     * Rmq constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        /** @var array config */
        $this->config = $config;

        $params = [
            $config['host'] ?? 'localhost',
            $config['port'] ?? 5672,
            $config['user'] ?? 'guest',
            $config['password'] ?? 'guest',
            $config['vhost'] ?? '/',
            isset($config['params']) ? array_values($config['params']) : [],
        ];

        $this->init(...$params);
    }

    /**
     * @param $host
     * @param $port
     * @param $user
     * @param $password
     * @param string $vhost
     * @param array $params
     *
     * @return $this
     */
    public function init($host, $port, $user, $password, $vhost = '/', array $params = [])
    {
        if (!defined('AMQP_WITHOUT_SIGNALS')) {
            define('AMQP_WITHOUT_SIGNALS', true);
        }
        $this->connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost, ...$params);
        $this->channel = $this->connection->channel(1);
        $this->channel->basic_qos(0, $this->config['basic.qos'] ?? self::BASIC_QOS, false);

        return $this;
    }

    /**
     * @param $message
     * @param string $queueName
     *
     * @return RmqHelper
     *
     * @throws AMQPProtocolChannelException
     */
    public function publish($message, string $queueName): RmqHelper
    {
        if (empty($queueName)) {
            throw new AMQPInvalidArgumentException('queueName must be set');
        }

        try {
            $this->doPublish($message, $queueName);

        } catch (AMQPProtocolChannelException $channelException) {
            throw $channelException; // это ошибка несуществующей очереди, выбрасываем дальше

        } catch (\Exception $exception) {
            $this->reconnect();
            $this->doPublish($message, $queueName);
        }

        return $this;
    }

    private function doPublish($message, string $queueName): RmqHelper
    {
        if (\is_array($message)) {
            $message = json_encode($message);
        }

        Log::error($message);

        $message = $this->makeMessage($message);

        $this->channel->basic_publish($message, $queueName, $queueName);

        return $this;
    }

    /**
     * @param string $message
     *
     * @return AMQPMessage
     */
    private function makeMessage(string $message): AMQPMessage
    {
        return new AMQPMessage($message,
            ['content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
    }

    private function reconnect(): void
    {
        $this->connection->reconnect();
        $this->channel = $this->connection->channel();
    }

    /**
     * @param callable $callback Функция, выполняющая обработку задачи
     * @param string $queue Очередь, из которой читаем задачи
     * @param string $tag Тэг, характеризующий обработчик для отображения в консоли RabbitMQ
     * @param int $timeoutSeconds Таймаут ожидания новой задачи. 0 =
     */
    public function loopConsume($callback, string $queue, string $tag = '', int $timeoutSeconds = 0): void
    {
        $this->channel->basic_consume($queue, $tag, false, false, false, false, $callback);

        $this->loop($timeoutSeconds);
    }

    /**
     * @param int $timeoutSeconds Таймаут ожидания новых задач. Если 0 - бесконечное ожидание
     */
    private function loop(int $timeoutSeconds = 0)
    {
        while (count($this->channel->callbacks)) {
            if (0 === $timeoutSeconds) {
                $this->channel->wait();
            } else {
                try {
                    $this->channel->wait(null, false, $timeoutSeconds);
                } catch (AMQPTimeoutException $e) {
                    return;
                }
            }
        }
    }

    /**
     * @param string $exchange
     * @param string $queue
     * @param string $routing
     *
     * @return RmqHelper
     */
    public function bindQueue(string $queue, string $exchange, string $routing = ''): RmqHelper
    {
        $this->channel->queue_bind($queue, $exchange, $routing);

        return $this;
    }

    /**
     * @param string $queue
     * @param string $exchange
     * @param string $routing
     *
     * @return RmqHelper
     */
    public function unBindQueue(string $queue, string $exchange, string $routing = ''): RmqHelper
    {
        $this->channel->queue_unbind($queue, $exchange, $routing);

        return $this;
    }

    /**
     * @param string $queue
     *
     * @return $this
     */
    public function declareQueue(string $queue)
    {
        $this->channel->queue_declare($queue, false, true, false, false);

        return $this;
    }

    /**
     * @param string $exchange
     * @param string $type
     *
     * @return $this
     */
    public function declareExchange(string $exchange, $type = 'direct')
    {
        $this->channel->exchange_declare($exchange, $type, false, true, false);

        return $this;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}