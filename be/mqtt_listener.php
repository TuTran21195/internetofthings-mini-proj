<?php

require_once __DIR__ . '/../vendor/bluerhinos/phpmqtt/phpMQTT.php'; // Load thư viện phpMQTT
require_once __DIR__ . '/../vendor/autoload.php';  // Tự động load các thư viện đã cài qua Composer

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Ratchet\Client\WebSocket;
use React\EventLoop\Factory;
use Ratchet\Client\Connector;

$server   = '192.168.180.195';  // Địa chỉ MQTT broker của bạn: ipconfig trên cmd laptop rồi copy cái ipv4 của cái Wifi vào đây: 
    //cái địa chỉ mqtt này mà chạy trên local thì cần phải giống cái ip trên code andruino của ESP32
$port = 2003;                // Cổng MQTT
$clientId = 'php-mqtt-listener';     // ID client MQTT


// Tạo vòng lặp sự kiện ReactPHP
$loop = Factory::create();
$connector = new Connector($loop);

// Kết nối tới broker MQTT
$mqtt = new MqttClient($server, $port, $clientId);

// Cài đặt kết nối MQTT
$connectionSettings = (new ConnectionSettings)
    ->setUsername('doanthitramy')  // Tên đăng nhập MQTT
    ->setPassword('123')           // Mật khẩu MQTT
    ->setKeepAliveInterval(60)     // Giữ kết nối trong 60 giây
    ->setLastWillTopic('dulieu')   // Đặt topic nếu mất kết nối
    ->setLastWillMessage('Client disconnected') // Tin nhắn khi mất kết nối
    ->setLastWillQualityOfService(0);

// Hàm xử lý khi nhận được message từ MQTT
$mqtt->subscribe('dulieu', function ($topic, $message) use ($connector) {
    echo sprintf("Received message on topic [%s]: %s\n", $topic, $message);

    // Xử lý chuỗi message nhận được
    if (strpos($message, 'Humidity: ') !== false) {
        preg_match('/Humidity:\s(\d+)\s%.*Temperature:\s([\d.]+)\s\*C.*Light\sLevel:\s(\d+)\slux/', $message, $matches);

        if ($matches) {
            $humidity = $matches[1];    // Lấy giá trị độ ẩm
            $temperature = $matches[2]; // Lấy giá trị nhiệt độ
            $lux = $matches[3];         // Lấy giá trị ánh sáng
            $time = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại

            // Lưu dữ liệu vào cơ sở dữ liệu
            saveToDatabase($humidity, $temperature, $lux, $time);

            // Gửi tín hiệu qua WebSocket để cập nhật biểu đồ
            $connector('ws://localhost:8081/ws', [], ['Origin' => 'http://localhost'])
                ->then(function(WebSocket $conn) use ($humidity, $temperature, $lux) {
                    $data = json_encode([
                        'humidity' => $humidity,
                        'temperature' => $temperature,
                        'lux' => $lux
                    ]);

                    $conn->send($data); // Gửi dữ liệu qua WebSocket
                    $conn->close();
                }, function($e) {
                    echo "Không thể kết nối WebSocket: {$e->getMessage()}\n";
                });
        }
    } elseif (strpos($message, 'led1 on') !== false) {
        echo "Nhận lệnh: Bật đèn LED 1\n";
    } elseif (strpos($message, 'turned on') !== false) {
        echo "Đèn LED 1 đã bật thành công\n";
    }
}, 0); // Độ ưu tiên QoS 0

// Kết nối MQTT và bắt đầu lắng nghe
$mqtt->connect($connectionSettings, true);
$mqtt->loop(true); // Lắng nghe liên tục

// Hàm lưu dữ liệu vào cơ sở dữ liệu
function saveToDatabase($humidity, $temperature, $lux, $time) {
    $host = 'localhost'; // Địa chỉ database
    $db = 'iot_database'; // Tên database
    $user = 'root';        // Tài khoản database
    $pass = '';            // Mật khẩu database (nếu có)

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Chèn dữ liệu vào bảng sensor_data
        $stmt = $pdo->prepare("INSERT INTO sensor_data (humidity, temperature, lux, time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$humidity, $temperature, $lux, $time]);

        echo "Đã lưu dữ liệu vào cơ sở dữ liệu.\n";
    } catch (PDOException $e) {
        echo "Lỗi khi lưu dữ liệu: " . $e->getMessage() . "\n";
    }
}

// Chạy vòng lặp sự kiện ReactPHP
$loop->run();
