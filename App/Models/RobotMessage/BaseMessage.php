<?php

namespace App\Models\RobotMessage;

use App\Tools;
use Exception;

abstract class BaseMessage
{
    use Tools;

    public string $conversationId;
    public string $conversationType;
    public string $senderId;
    public string $senderNick;
    public string $sessionWebhook;
    public string $timestamp;
    public string $msgtype;
    public array $content;

    /**
     * @throws Exception
     */
    public function __construct($body = 'fuck')
    {
        if ($body === 'fuck') {
            $body = json_decode(file_get_contents('php://input'));
        }
        $this->conversationId = $body->conversationId;
        $this->conversationType = $body->conversationType;
        $this->senderId = $body->senderId;
        $this->senderNick = $body->senderNick;
        $this->sessionWebhook = $body->sessionWebhook;
        $this->msgtype = $body->msgtype;
        $this->content = $body->content;
        $headers = $this->parseHeaders();
        $this->timestamp = $headers['timestamp'];
        if ($this->timestamp < time() - 60 * 60 * 24) {
            throw new Exception('timestamp error');
        }
    }
}