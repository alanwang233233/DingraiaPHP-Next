<?php

namespace App\Controller;
class GetBody
{
    public array $body;

    public function __construct()
    {
        $this->body = json_decode(file_get_contents('php://input'));
    }
}