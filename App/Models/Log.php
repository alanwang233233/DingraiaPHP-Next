<?php

namespace App\Models;

use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class Log
{
    public Logger $Logger;
    private Logger $RequestLogger;

    public function __construct()
    {
        $logger = new Logger('Logger');
        $logger->pushHandler(new StreamHandler('../../Logs/System.log', Level::Debug));
        $logger->pushHandler(new FirePHPHandler());
        // $logger->info('Request Logger Ready!');
        $this->Logger = $logger;
        $RequestLogger = new Logger('RequestLogger');
        $RequestLogger->pushHandler(new StreamHandler('../../Logs/Request.log', Level::Debug));
        $RequestLogger->pushHandler(new FirePHPHandler());
        // $RequestLogger->info('Request Logger Ready!');
        $this->RequestLogger = $RequestLogger;
    }

    public function OnRequest(): void
    {
        $this->RequestLogger->info("OnRequest:" /*. $this->RequestInfo()*/);
    }

    /*private function RequestInfo(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $requestUri = $_SERVER['REQUEST_URI'];
        $fullUrl = $protocol . '://' . $host . $requestUri;
        $method = $_SERVER['REQUEST_METHOD'];
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        $getParams = $_GET;
        $postParams = $_POST;
        $rawData = file_get_contents('php://input');
        $clientIp = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '未知';
        $requestInfo = [
            'url' => $fullUrl,
            'method' => $method,
            'headers' => $headers,
            'get_params' => $getParams,
            'post_params' => $postParams,
            'raw_data' => $rawData,
            'client_ip' => $clientIp,
            'user_agent' => $userAgent
        ];
        header('Content-Type: application/json');
        return json_encode($requestInfo);
    }*/

    public function New(string $msg, string $level): void
    {
        $this->Logger->log($level, $msg);
    }
}