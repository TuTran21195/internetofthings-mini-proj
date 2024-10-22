<?php
// nhận yêu cầu bật tắt đèn từ FE sau đó gửi tín hiệu cho HW thông qua MQTT
// Chờ đợi msg message.device === device && message.status === `${action} success` ({device: led1; status: on success})rồi thì lưu thao tác đó vào CSDL & gửi tín hiệu thành công cho FE

require_once __DIR__ . '/../vendor/autoload.php';  // Tự động load các thư viện đã cài qua Composer

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Ratchet\Client\WebSocket;
use React\EventLoop\Factory;
use Ratchet\Client\Connector;

//nhận yêu cầu bật tắt đèn từ FE 
$data = json_decode(file_get_contents('php://input'), true);
$device = $data['device'];
$action = $data['action'];

$server   = '192.168.1.7';  // Địa chỉ MQTT broker của bạn: ipconfig trên cmd laptop rồi copy cái ipv4 của cái Wifi vào đây: 
    //cái địa chỉ mqtt này mà chạy trên local thì cần phải giống cái ip trên code andruino của ESP32
$port = 2003;                // Cổng MQTT
$clientId = 'php-mqtt-listener2';     // ID client MQTT: cái này nó phải khác cái ID bên mqtt_listener.php vì 
                                    //nếu không nó sẽ không phân biệt được là đóng kết nối mqtt với von client nào

// Cài đặt kết nối MQTT
$connectionSettings = (new ConnectionSettings)
    ->setUsername('doanthitramy')  // Tên đăng nhập MQTT
    ->setPassword('123')           // Mật khẩu MQTT
    ->setKeepAliveInterval(60)     // Giữ kết nối trong 60 giây
    ->setLastWillTopic('dulieu')   // Đặt topic nếu mất kết nối
    ->setLastWillMessage('Client disconnected') // Tin nhắn khi mất kết nối
    ->setLastWillQualityOfService(0);


// Send the message to the MQTT broker 
$mqttClient = new MqttClient($server, $port, $clientId);
$mqttClient->connect($connectionSettings, true); // Kết nối tới broker MQTT
$mqttClient->publish('dulieu', "$device $action", 0); //gửi "led1 on" lên topic dulieu
$mqttClient->disconnect();

echo json_encode(['success' => true]);
?>
