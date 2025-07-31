<?php /** @noinspection SpellCheckingInspection */

namespace App\Dingraia\Stream;
require_once __DIR__ . '/../Vendor/autoload.php';

use Workerman\Connection\AsyncTcpConnection;
use Workerman\Worker;

class Stream
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $endpoint;
    protected string $ticket;
    protected AsyncTcpConnection $wsConnection;
    /*    protected array $subscriptions = [
            [
                'type' => 'EVENT',
                'topic' => '*'
            ],
            [
                'type' => 'CALLBACK',
                'topic' => '/v1.0/im/bot/messages/get'
            ]
        ];*/
    protected array $subscriptions;

    public function __construct($clientId, $clientSecret, $subscriptions = [
        [
            'type' => 'EVENT',
            'topic' => '*'
        ],
        [
            'type' => 'CALLBACK',
            'topic' => '/v1.0/im/bot/messages/get'
        ]
    ])
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->subscriptions = $subscriptions;
    }

    public function run(): void
    {
        if (file_exists("alive.lock")) {
            /*            echo json_encode([
                            'error' => true,
                            'msg' => 'Stream already running, or it has not been killed properly.It\'s a good idea to check its status before deleting alive.lock to force start the daemon.',
                        ]);
                        exit;*/
        } else {
            file_put_contents("alive.lock", "1");
        }
        ignore_user_abort(true);
        set_time_limit(0);
        $worker = new Worker();
        $worker->onWorkerStart = function () {
            $this->registerConnection();
            $this->connectWebSocket();
        };
        Worker::runAll();
    }

    public function registerConnection(): void
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://4.ipw.cn/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $ip = trim($response);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.dingtalk.com/v1.0/gateway/connections/open',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "clientId": "' . $this->clientId . '",
                "clientSecret": "' . $this->clientSecret . '",
                "subscriptions": ' . json_encode($this->subscriptions) . ',
                "ua": "dingraia-php-next/0.0.1",
                "localIp": "' . $ip . '"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);
        $this->endpoint = str_replace('wss://', 'ws://', $response['endpoint']);
        $this->ticket = $response['ticket'];
    }

    /**
     * 建立WebSocket连接
     */
    protected function connectWebSocket(): void
    {
        $wsUrl = $this->endpoint . "?ticket=" . $this->ticket;
        $this->wsConnection = new AsyncTcpConnection($wsUrl);
        $this->wsConnection->transport = 'ssl';

        $this->wsConnection->onConnect = function ($con) {
            echo "WebSocket connected: " . $con->getRemoteIp() . "\n";
        };

        $this->wsConnection->onMessage = function ($con, $data) {
            $message = json_decode($data, true);
            switch ($message['type']) {
                case 'SYSTEM':
                    $this->handleSystemMessage($message);
                    break;
                case 'EVENT':
                    $this->handleEventMessage($message);
                    break;
                case 'CALLBACK':
                    $this->handleCallbackMessage($message);
                    break;
            }
        };

        $this->wsConnection->onClose = function () {
            echo "WebSocket closed\n";
        };

        $this->wsConnection->onError = function ($con, $code, $msg) {
            echo "WebSocket error: $code - $msg\n";
        };

        $this->wsConnection->connect();
    }

    /**
     * 处理系统消息（ping/disconnect）
     */
    protected function handleSystemMessage($message): void
    {
        switch ($message['headers']['topic']) {
            case 'ping':
                $response = [
                    'code' => 200,
                    'headers' => [
                        'messageId' => $message['headers']['messageId'],
                        'contentType' => 'application/json'
                    ],
                    'message' => 'OK',
                    'data' => $message['data']
                ];
                $this->wsConnection->send(json_encode($response));
                return;

            case 'disconnect':
                echo "Disconnect received: " . $message['data'] . "\n";
                //服务端静默10s后自动关闭连接，因此不必手动关闭连接
                //$this->wsConnection->close();
                return;
            default:
                echo "Unknown system message type: " . $message['headers']['topic'] . "\n";
        }
    }

    /**
     * 处理事件消息（如考勤事件）
     */
    protected function handleEventMessage($message): void
    {
        echo "Received event: " . $message['headers']['eventType'] . "\n";

        // 业务处理逻辑
        $processResult = $this->processEvent($message);

        // 构建响应
        $response = [
            'code' => 200,
            'headers' => [
                'messageId' => $message['headers']['messageId'],
                'contentType' => 'application/json'
            ],
            'message' => 'OK',
            'data' => json_encode([
                'status' => $processResult ? 'SUCCESS' : 'LATER',
                'message' => 'Processed'
            ])
        ];

        $this->wsConnection->send(json_encode($response));
    }


    // === 业务处理示例 ===

    protected function processEvent($event): true
    {
        file_put_contents('events.log', json_encode($event) . "\n", FILE_APPEND);
        return true;
    }

    /**
     * 处理回调消息（如机器人消息）
     */
    protected function handleCallbackMessage($message): void
    {
        echo "Received callback: " . $message['headers']['topic'] . "\n";
        $callbackResponse = $this->processCallback($message);
        $response = [
            'code' => 200,
            'headers' => [
                'messageId' => $message['headers']['messageId'],
                'contentType' => 'application/json'
            ],
            'message' => 'OK',
            'data' => json_encode($callbackResponse)
        ];

        $this->wsConnection->send(json_encode($response));
    }

    // === 启动方法 ===

    protected function processCallback($callback): array
    {
        file_put_contents('callbacks.log', json_encode($callback) . "\n", FILE_APPEND);
        return ['status' => 'success'];
    }

    public function __destruct()
    {
        $this->stop();
        if (file_exists("alive.lock")) {
            unlink("alive.lock");
        }
    }

    public function stop(): void
    {

    }
}
