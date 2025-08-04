<?php

namespace App\Middleware;

use App\Models\Log;
use App\Dingraia\Interface\MiddlewareInterface;

class RequestLog implements MiddlewareInterface
{
    public function handle($next): mixed
    {
        $log = new Log();
        $log->OnRequest();
        return $next();
    }
}
