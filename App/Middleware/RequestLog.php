<?php

namespace App\Middleware;

use App\Models\Log;

$log = new Log();
$log->OnRequest();
