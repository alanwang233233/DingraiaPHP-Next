<?php

namespace App\Models\Message;

use Exception;

class Text extends BaseMessage
{
    public string $text;


    /**
     * @throws Exception
     */
    public function __construct($body = 'fuck')
    {
        parent::__construct($body);
        if ($this->msgtype !== 'text') {
            throw new Exception('msgtype must be text');
        }
        $this->text = $this->content->text;
    }
}