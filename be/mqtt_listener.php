<?php
require('/../vendor/bluerhinos/phpmqtt/phpMQTT.php');

// Kết nối tới MQTT Broker
$server = '192.168.164.195';  // Địa chỉ MQTT broker của bạn
$port = 2003;                // Cổng MQTT
$username = 'doanthitramy';               // Username
$password = '123';               // Password nếu cần
$client_id = 'phpMQTT-Client'; // Tên client

$mqtt = new phpMQTT($server, $port, $client_id);

if(!$mqtt->connect(true, NULL, $username, $password)) {
    exit(1); // Kết nối thất bại
}

// Đăng ký lắng nghe topic 'dulieu'. Khi có thông điệp mới trên topic này, hàm procmsg sẽ được gọi
$topics['dulieu'] = array('qos' => 0, 'function' => 'procmsg');
$mqtt->subscribe($topics, 0);

// Hàm xử lý tin nhắn từ topic 'dulieu'
function procmsg($topic, $msg){
    // Kết nối cơ sở dữ liệu
    $db = new mysqli('localhost', 'root', '', 'iot_database');

     // Kiểm tra nếu payload chứa chuỗi báo lỗi "Loi doc cam bien DHT11!"
     if (strpos($msg, 'Loi doc cam bien DHT11!') !== false) {
        // Nếu DHT11 lỗi, chỉ lấy giá trị ánh sáng
        preg_match('/Light Level: (\d+) lux/', $msg, $matches);
        $humidity = -1;     // Lấy giá trị độ ẩm = -1 nếu lỗi cảm biến
        $temperature = -1;  // Lấy giá trị nhiệt độ = -1 nếu lỗi cảm biến
        $lux = $matches[1];  // Lấy giá trị ánh sáng

        // Lưu dữ liệu vào bảng (humidity và temperature có thể lưu -1 (hoặc để NULL) nếu lỗi DHT11)
        $db->query("INSERT INTO sensor_data (humid, bright, temperature, timestamp) 
        VALUES ('$humidity', '$lux', '$temperature', NOW())");
    } elseif (strpos($msg, 'Humidity:') !== false) {
        // Nếu payload đầy đủ dữ liệu từ cảm biến
        preg_match('/Humidity: (\d+\.?\d*) % Temperature: (\d+\.?\d*) \*C Light Level: (\d+) lux/', $msg, $matches);
        $humidity = $matches[1];     // Lấy giá trị độ ẩm
        $temperature = $matches[2];  // Lấy giá trị nhiệt độ
        $lux = $matches[3];          // Lấy giá trị ánh sáng
        
        // Lưu dữ liệu vào bảng (humidity và temperature có thể lưu -1 (hoặc để NULL) nếu lỗi DHT11)
        $db->query("INSERT INTO sensor_data (humid, bright, temperature, timestamp) 
        VALUES ('$humidity', '$lux', '$temperature', NOW())");
    } else {
        // Nếu thông điệp là lệnh điều khiển LED hoặc phản hồi trạng thái LED
        handleLEDMessage($msg);
    }
    

    // Đóng kết nối cơ sở dữ liệu
    $db->close();

    // Gửi tín hiệu cho client WebSocket
    /// Gửi dữ liệu mới qua WebSocket tới client
    sendUpdateSignal($humidity, $temperature, $lux);
}

// Hàm gửi tín hiệu cập nhật cho client WebSocket
function sendUpdateSignal() {
    $data = array(
        'humidity' => $humidity,
        'temperature' => $temperature,
        'light' => $lux
    );
    // Kết nối WebSocket server để gửi dữ liệu mới
    $client = new WebSocket\Client("ws://localhost:8080"); // Thay bằng URL WebSocket server của bạn
    $client->send(json_encode($data)); // Gửi dữ liệu mới nhận qua WebSocket
    $client->close();
}

function handleLEDMessage($msg) {
    // Kết nối WebSocket để gửi lệnh điều khiển LED hoặc phản hồi trạng thái
    $client = new WebSocket\Client("ws://localhost:8080");

    // Tạo dữ liệu phản hồi cho client
    $data = array(
        'message' => $msg  // Gửi nguyên chuỗi lệnh điều khiển LED hoặc phản hồi
    );

    // Gửi dữ liệu qua WebSocket
    $client->send(json_encode($data));

    // Đóng kết nối WebSocket
    $client->close();
}

$mqtt->loopForever(); // Chạy vòng lặp để lắng nghe liên tục
