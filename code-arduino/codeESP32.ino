#include <Adafruit_Sensor.h>
#include <DHT.h>
#include <DHT_U.h>
#include <WiFi.h>

#include <PubSubClient.h>

#define DHTPIN 22      // Chân Data của DHT11 nối với GPIO 22 (D2)
#define DHTTYPE DHT11 // Loại cảm biến DHT11

#define LIGHT_SENSOR_PIN 34 // Chân Analog Out của cảm biến ánh sáng nối với GPIO 22 (D22)
#define LED1_PIN 18           // Chân dương của đèn LED nối với GPIO 18 (D18)
#define LED2_PIN 19           // Chân dương của đèn LED nối với GPIO 19 (D19)
#define LED3_PIN 21           // Chân dương của đèn LED nối với GPIO 21 (D21)

DHT dht(DHTPIN, DHTTYPE);

bool ledState = false; // Trạng thái của đèn LED

// Thông tin Wi-Fi: Tên wifi & password
const char* ssid = "TrMyWF";  
const char* password = "12345678";

// Thông tin MQTT: đổi cổng sang 2003 và username + password
const char* mqttServer = "192.168.157.195"; // ipconfig trên cmd laptop rồi copy cái ipv4 của cái Wifi vào đây
const int mqttPort = 2003; // Thay đổi cổng nếu cần
const char* mqttUser = "doanthitramy";
const char* mqttPassword = "123";

WiFiClient espClient;
PubSubClient client(espClient);

// Hàm để kết nối đến Wi-Fi
void setup_wifi() {
  delay(10);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    // Serial.print(".");
  }
  Serial.println("Connected to WiFi");
}

// Hàm nhận tin nhắn từ MQTT để bật tắt đèn
void callback(char* topic, byte* message, unsigned int length) {
  String command;
  for (int i = 0; i < length; i++) {
    command += (char)message[i];
  }

  // Serial.print("Received message: ");
  // Serial.println(command);  // In ra tin nhắn nhận được ( muốn xem có lỗi ko thì bật 2 cái này lên)

  // Serial.print(" on topic: ");
  // Serial.println(topic); // In ra topic


    // Kiểm tra xem topic có phải là "dulieu" không-------------------------------------
    if (String(topic) == "dulieu") {
      if (command == "led1 on") { // dùng led1 on, led1 off để bật tắt đèn 1. Tương tự với các đèn khác
        digitalWrite(LED1_PIN, HIGH);
        // Serial.println("LED 1 is ON");
        client.publish("dulieu", "turned led1 on");
      } else if (command == "led1 off") {
        digitalWrite(LED1_PIN, LOW);
        // Serial.println("LED 1 is OFF");
        client.publish("dulieu", "turned led1 off");
      } else if (command == "led2 on") {
        digitalWrite(LED2_PIN, HIGH);
        // Serial.println("LED 2 is ON");
        client.publish("dulieu", "turned led2 on");
      } else if (command == "led2 off") {
        digitalWrite(LED2_PIN, LOW);
        // Serial.println("LED 2 is OFF");
        client.publish("dulieu", "turned led2 off");
      } else if (command == "led3 on") {
        digitalWrite(LED3_PIN, HIGH);
        // Serial.println("LED 3 is ON");
        client.publish("dulieu", "turned led3 on");
      } else if (command == "led3 off") {
        digitalWrite(LED3_PIN, LOW);
        // Serial.println("LED 3 is OFF");
        client.publish("dulieu", "turned led3 off");
      }
      else if (command == "led all on"){
        digitalWrite(LED1_PIN, HIGH);
        digitalWrite(LED2_PIN, HIGH);
        digitalWrite(LED3_PIN, HIGH);
        // Serial.println("LED ALL is ON");
      } 
      else if (command == "led all off"){
        digitalWrite(LED1_PIN, LOW);
        digitalWrite(LED2_PIN, LOW);
        digitalWrite(LED3_PIN, LOW);
        // Serial.println("LED ALL is OFF");
      }     

    }
  
    
}


void setup() {
  Serial.begin(115200);
  dht.begin();
  //pinMode(LIGHT_SENSOR_PIN, INPUT);
  analogReadResolution(12);  // Đặt độ phân giải ADC về 12-bit (0 - 4095)
  pinMode(LED1_PIN, OUTPUT);
  pinMode(LED2_PIN, OUTPUT);
  pinMode(LED3_PIN, OUTPUT);

  setup_wifi();
  client.setServer(mqttServer, mqttPort);
  client.setCallback(callback);

}

void reconnect() {
  while (!client.connected()) {
    if (client.connect("ESP32Client", mqttUser, mqttPassword)) {
      Serial.println("Connected to MQTT Broker!");
      client.subscribe("dulieu"); // Đăng ký topic điều khiển LED ----------------------------------------
      Serial.println("Subscribed to topic: dulieu");
    } else {
      Serial.print("Khong ket noi dc voi MQTT Broker, return code: ");
      Serial.println(client.state());
      delay(1000);
    }
  }
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();
  // Đọc dữ liệu từ DHT11
  float humidity = dht.readHumidity();
  float temperature = dht.readTemperature();

  // Đọc dữ liệu từ cảm biến ánh sáng
  int lightLevel = analogRead(LIGHT_SENSOR_PIN);
  int lux = map(lightLevel, 0, 4095, 1000, 0); // Giả lập giá trị lux (quy đổi trông cho trực quan hơn chứ không chính xác)
                                                // Giá trị tối đa 1000 lux, tối thiểu 0 lux


  if (isnan(humidity) || isnan(temperature)) {
    String payload = String("Loi doc cam bien DHT11! - ")  + " Light Level:" + lux + " lux";
    client.publish("dulieu", payload.c_str()); 
  } else {
    // Gửi dữ liệu lên MQTT
    String payload = String("Humidity: ") + humidity + " % Temperature: " + temperature + " *C" + " Light Level:" + lux + " lux";
    client.publish("dulieu", payload.c_str()); // Gửi dữ liệu lên topic -------------------------------------
  }


  delay(2000); // Đợi 2 giây trước khi đọc lại dữ liệu
}
