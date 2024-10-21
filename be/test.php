<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload từ Composer
use Ratchet\Client\Connector;
use React\EventLoop\Factory;

$loop = Factory::create();
$connector = new Connector($loop);

$connector('ws://localhost:8081/ws')
    ->then(function($conn) {
        echo "Kết nối WebSocket thành công\n";
        $conn->send('Test từ PHP');
        $conn->close();
    }, function($e) {
        echo "Không thể kết nối WebSocket: {$e->getMessage()}\n";
    });

$loop->run();