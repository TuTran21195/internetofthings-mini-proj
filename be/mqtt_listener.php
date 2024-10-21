<?php

require_once __DIR__ . '/../vendor/bluerhinos/phpmqtt/phpMQTT.php'; // Load thư viện phpMQTT
require_once __DIR__ . '/../vendor/autoload.php';  // Tự động load các thư viện đã cài qua Composer

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Ratchet\Client\WebSocket;
use React\EventLoop\Factory;
use Ratchet\Client\Connector;

$server   = '192.168.1.7';  // Địa chỉ MQTT broker của bạn: ipconfig trên cmd laptop rồi copy cái ipv4 của cái Wifi vào đây: 
    //cái địa chỉ mqtt này mà chạy trên local thì cần phải giống cái ip trên code andruino của ESP32
$port = 2003;                // Cổng MQTT
$clientId = 'php-mqtt-listener';     // ID client MQTT


// Tạo vòng lặp sự kiện ReactPHP cho việc liên tục lắng nghe MQTT và Websocket
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


// Kết nối MQTT và bắt đầu lắng nghe
try {
    $mqtt->connect($connectionSettings, true);  // Establish connection
    echo "ket noi mqtt thanh cong\n"; 

    // Hàm xử lý khi nhận được message từ MQTT
    $mqtt->subscribe('dulieu', function ($topic, $message) use ($connector,$loop) {
        echo sprintf("Received message on topic [%s]: %s\n", $topic, $message);

        // Xử lý chuỗi message nhận được
        if (strpos($message, 'Humidity: ') !== false) { //Chuỗi vd Humidity: 68 % Temperature: 23 *C Light Level: 320 lux
            // mosquitto_pub -p 2003 -t "dulieu" -u "doanthitramy" -P "123" -m "Humidity: 58 % Temperature: 39 *C Light Level: 310 lux"
            preg_match('/Humidity:\s(\d+)\s%.*Temperature:\s([\d.]+)\s\*C.*Light\sLevel:\s(\d+)\slux/', $message, $matches);

            if ($matches) {
                $humidity = $matches[1];    // Lấy giá trị độ ẩm
                $temperature = $matches[2]; // Lấy giá trị nhiệt độ
                $lux = $matches[3];         // Lấy giá trị ánh sáng
                date_default_timezone_set('Asia/Ho_Chi_Minh'); // Thiết lập múi giờ Việt Nam
                $time = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại

                // Lưu dữ liệu vào cơ sở dữ liệu
                saveToDatabase($humidity, $temperature, $lux, $time);

                // Sau khi lưu vào database, kết nối WebSocket để gửi dữ liệu
                // Gửi tín hiệu qua WebSocket để cập nhật biểu đồ
                $updateChartMsg = json_encode(['command' => 'updatechart' ]);
                sendDataOverWebSocket($connector, $loop, $updateChartMsg);
            }
        } elseif (strpos($message, 'Loi doc cam bien DHT11! - ') !== false) { // Loi doc cam bien DHT11! - Light Level: 300 lux
            //mosquitto_pub -p 2003 -t "dulieu" -u "doanthitramy" -P "123" -m "Loi doc cam bien DHT11! - Light Level: 300 lux"
            preg_match('/Light\sLevel:\s(\d+)\slux/', $message, $matches);
            if ($matches) {
                $lux = $matches[1];  // Lấy giá trị ánh sáng]
                echo "anh sang do duoc: $lux";
                $humidity = -1;    // Lấy giá trị độ ẩm
                $temperature = -1; // Lấy giá trị nhiệt độ
                date_default_timezone_set('Asia/Ho_Chi_Minh'); // Thiết lập múi giờ Việt Nam
                $time = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại
                // Lưu dữ liệu vào cơ sở dữ liệu
                saveToDatabase($humidity, $temperature, $lux, $time);

                // Sau khi lưu vào database, kết nối WebSocket để gửi dữ liệu
                // Gửi tín hiệu qua WebSocket để cập nhật biểu đồ
                $updateChartMsg = json_encode(['command' => 'updatechart' ]);
                sendDataOverWebSocket($connector, $loop, $updateChartMsg);
            }
        } elseif (strpos($message, 'turned ') !== false) { // respond to action request: turned on/off led1/2/3 vd: turned on led1 -> bật tắt thành công!
            //lưu action vào csdl sau khi đã nhận đc tín hiệu bật thành công
            echo "nhan duoc lenh bat tat";
            if (preg_match('/\b(\w+)\s+(\w+)$/', $message, $matches)) {
                $device = $matches[1]; // led1, led2, hoặc led3
                $action = $matches[2]; // on hoặc off
            
                saveActionToDB($device, $action); 

                $respondMsg = json_encode([
                    'device' => $device,
                    'status' => $action.' success' ]);
                echo "lenh: $respondMsg";
                //gửi tín hiệu thành công đến FE
                sendDataOverWebSocket($connector, $loop, $message); // đổi thành $respondMsg
            }
            
        }
    }, 0); // Độ ưu tiên QoS 0

    while (true) {
        $mqtt->loop();  // Continuously listen for new messages
        usleep(100000); // Delay to avoid high CPU usage
        echo "dang lien tuc nghe topic dulieu\n";
    }
} catch (Exception $e) {
    echo "MQTT connection error: " . $e->getMessage() . "\n";
}

// Hàm lưu dữ liệu vào cơ sở dữ liệu
function saveToDatabase($humidity, $temperature, $lux, $time) {
    $host = 'localhost'; // Địa chỉ database
    $db = 'iot_database'; // Tên database
    $user = 'root';        // Tài khoản database
    $pass = '';            // Mật khẩu database (nếu có)

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Chèn dữ liệu vào bảng tbl_data_sensor
        $stmt = $pdo->prepare("INSERT INTO `tbl_data_sensor` (`id`, `humid`, `bright`, `temperature`, `time`) VALUES (NULL, ?, ?, ?, ?)");
        $stmt->execute([$humidity, $lux, $temperature, $time]);

        echo "Đã lưu dữ liệu vào cơ sở dữ liệu.\n";
    } catch (PDOException $e) {
        echo "Lỗi khi lưu dữ liệu: " . $e->getMessage() . "\n";
    }
}

function saveActionToDB($device, $action){
    $host = 'localhost'; // Địa chỉ database
    $db = 'iot_database'; // Tên database
    $user = 'root';        // Tài khoản database
    $pass = '';            // Mật khẩu database (nếu có)

    try {
        // Save the action to the database
        $pdo = new PDO("mysql:host=localhost;dbname=iot_database", "root", "");
        $stmt = $pdo->prepare("INSERT INTO `tbl_action_history` (`id`, `devices`, `action`, `time`) VALUES (NULL, ? , ? , NOW())");

        $stmt->execute([$device, $action]);

        echo "Đã lưu Action vào cơ sở dữ liệu.\n";
    } catch (PDOException $e) {
        echo "Lỗi khi lưu Action: " . $e->getMessage() . "\n";
    }
}

// Hàm kết nối và gửi tín hiệu lệnh đến FE thông qua WebSocket
function sendDataOverWebSocket($connector, $loop, $updateChartMsg) {
    $connector('ws://localhost:8081/ws')
    ->then(function($conn) use ($updateChartMsg){
        echo "Kết nối WebSocket thành công\n";
        // $conn->send('updatechart');
        $conn->send($updateChartMsg);
        $conn->close();
    }, function($e) {
        echo "Không thể kết nối WebSocket: {$e->getMessage()}\n";
    });
    $loop->run();
}

// Chạy vòng lặp sự kiện ReactPHP
$loop->run();
