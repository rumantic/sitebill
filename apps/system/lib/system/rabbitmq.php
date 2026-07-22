<?php
namespace system\lib\system;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{
    public static $instance;
    protected static $lastError;
    /**
     * @var \PhpAmqpLib\Channel\AbstractChannel|\PhpAmqpLib\Channel\AMQPChannel
     */
    private $channel;
    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * Obtain RabbitMQ instance
     *
     * @return RabbitMQ
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self ();
        }

        return self::$instance;
    }

    private function __construct()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                RABBITMQ_HOST,
                RABBITMQ_PORT,
                RABBITMQ_USER,
                RABBITMQ_PASS,
                '/',
                false,
                'AMQPLAIN',
                null,
                'en_US',
                5.0,
                5.0,
                null,
                true
            );
            $this->channel = $this->connection->channel();
            $this->channel->queue_declare(RABBITMQ_QUEUE, false, false, false, false);
        } catch (\Exception $e) {
            echo 'Unable to connect to RabbitMQ: ', $e->getMessage(), "\n";
            exit;
        }
    }

    public function publish ($message)
    {
        try {
            $this->connection->wait(null, true);
            $rabbitmq_msg = new AMQPMessage($message);
            $this->channel->basic_publish($rabbitmq_msg, '', RABBITMQ_QUEUE);
            return true;
        } catch (\Exception $e) {
            echo 'Exception while publish to RabbitMQ: ', $e->getMessage(), "\n";
        }
    }

    public function consume ($callback)
    {
        $this->channel->basic_consume(RABBITMQ_QUEUE, '', false, true, false, false, $callback);

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }
    }

    public function close()
    {
        try {
            if ( $this->channel ) {
                $this->channel->close();
                $this->connection->close();
            }
        } catch (\Exception $e) {
            echo 'Exception while close connection to RabbitMQ: ', $e->getMessage(), "\n";
        }
    }

    public function __destruct()
    {
        $this->close();
    }


}
