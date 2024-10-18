<?php
//file này giúp khởi chạy WebSocket server

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require dirname(__DIR__) . '/../vendor/autoload.php';

class SensorDataPusher implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Khi client kết nối
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Xử lý khi nhận tin nhắn từ client
        // $servername = "localhost";
        // $username = "root";
        // $password = "";
        // $dbname = "iot_database";
        $db = new mysqli('localhost', 'root', '', 'iot_database'); // Kết nối database
        $result = $db->query("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 10");
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $db->close();

        // Gửi dữ liệu tới tất cả các client
        foreach ($this->clients as $client) {
            $client->send(json_encode($data));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Khi client đóng kết nối
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        // Khi có lỗi xảy ra
        $conn->close();
    }
}

// Khởi tạo server WebSocket
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SensorDataPusher()
        )
    ),
    8080
);

$server->run();
