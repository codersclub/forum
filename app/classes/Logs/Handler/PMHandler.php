<?php

namespace Logs\Handler;

use Ibf;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Отправляет личные сообщения указанным адресатам
 * Class PMHandler
 * @package Logs\Handler
 */
class PMHandler extends AbstractProcessingHandler
{

    /**
     * PM Subject
     * @var string
     */
    protected $subject;
    /**
     * Recipients id
     * @var array
     */
    protected $recipients = [];
    /**
     * Sender id
     * @var int
     */
    protected $sender;

    /**
     * @param string $subject Private Mail subject
     * @param array|int $recipients Recipient's member ids
     * @param int $sender Sender's member id
     * @param integer $level The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($subject, array $recipients, $sender, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->subject    = $subject;
        $this->recipients = $recipients;
        $this->sender     = $sender;
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        $messages = array();

        foreach ($records as $record) {
            if ($record['level'] < $this->level) {
                continue;
            }
            $messages[] = $this->processRecord($record);
        }

        if (!empty($messages)) {
            $this->sendPM((string) $this->getFormatter()->formatBatch($messages));
        }
    }

    protected function write(array $record)
    {
        $this->sendPM($record['formatted']);
    }

    /**
     * Sends Private Mail
     * @param string $text PM text
     */
    protected function sendPM($text)
    {
        static $processing;
        if (!$processing) {
            if (Ibf::isApplicationRegistered()) {
                foreach ($this->recipients as $receiver) {
                    $processing = true;
                    Ibf::app()->functions->sendpm($receiver, $text, $this->subject, $this->sender, 1, 0, 0);
                    $processing = false;
                }
            }
        }
    }
}
