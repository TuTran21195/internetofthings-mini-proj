<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once __DIR__ . '/../vendor/autoload.php'; // Đường dẫn tới autoload.php

class MyWebSocketServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Khi có một kết nối mới
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Khi nhận được thông điệp từ client
        echo sprintf('Connection %d sending message "%s"' . "\n", $from->resourceId, $msg);

        // Gửi lại cho tất cả client khác
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Khi một kết nối bị đóng
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        // Khi có lỗi
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Chạy WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MyWebSocketServer()
        )
    ),
    8081 // Cổng WebSocket
);

$server->run();
