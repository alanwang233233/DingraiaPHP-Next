<?php

namespace App\Models\RobotMessage;

use Exception;

class Text extends BaseMessage
{
    public string $text;


    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->msgtype !== 'text') {
            throw new Exception('msgtype must be text');
        }
        $this->text = $this->content->text;
    }
}