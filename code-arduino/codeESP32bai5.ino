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
#define LED4_PIN 23           // Chân dương của đèn LED nối với GPIO 23 (D23)

DHT dht(DHTPIN, DHTTYPE);

bool ledState = false; // Trạng thái của đèn LED

// Thông tin Wi-Fi: Tên wifi & password
const char* ssid = "TrMyWF";  
const char* password = "12345678";

// Thông tin MQTT: đổi cổng sang 2003 và username + password
const char* mqttServer = "192.168.108.195"; // ipconfig trên cmd laptop rồi copy cái ipv4 của cái Wifi vào đây
const int mqttPort = 2003; // Thay đổi cổng nếu cần
const char* mqttUser = "doanthitramy";
const char* mqttPassword = "123";

WiFiClient espClient;
PubSubClient client(espClient);
TaskHandle_t Task1;
bool windspeedHigh = false;


// Hàm để kết nối đến Wi-Fi
void setup_wifi() {
  delay(10);
  Serial.println("Connecting to WiFi");
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("Connected to WiFi");
}


// Hàm nhận tin nhắn từ MQTT để bật tắt đèn
void callback(char* topic, byte* message, unsigned int length) {
  String command;
  for (int i = 0; i < length; i++) {
    command += (char)message[i];
  }
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
  pinMode(LED4_PIN, OUTPUT);

  setup_wifi();
  client.setServer(mqttServer, mqttPort);
  client.setCallback(callback);

  xTaskCreatePinnedToCore(
    Task1code, /* Task function. */
    "Task1",   /* name of task. */
    10000,     /* Stack size of task */
    NULL,      /* parameter of the task */
    1,         /* priority of the task */
    &Task1,    /* Task handle to keep track of created task */
    0);        /* pin task to core 0 */  

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

bool led4Blinking = false;
unsigned long previousMillis = 0;  // Lưu thời gian để nhấp nháy
const long interval = 500;         // Tốc độ nhấp nháy LED (500ms)

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

  // Tạo giá trị ngẫu nhiên từ 0 đến 100
  int windspeed = random(0, 101);
  

  if (isnan(humidity) || isnan(temperature)) {
    String payload = String("Loi doc cam bien DHT11! - ")  + " Light Level:" + lux + " lux " + windspeed;
    client.publish("dulieu", payload.c_str()); 
  } else {
    // Gửi dữ liệu lên MQTT
    String payload = String("Humidity: ") + humidity + " % Temperature: " + temperature + " *C" + " Light Level:" + lux + " lux " + windspeed;
    client.publish("dulieu", payload.c_str()); // Gửi dữ liệu lên topic -------------------------------------
  }

  if (windspeed >= 60 && !windspeedHigh) {
    windspeedHigh = true;
    xTaskNotifyGive(Task1);
  } else if (windspeed < 60 && windspeedHigh) {
    windspeedHigh = false;
    vTaskDelete(Task1);
    digitalWrite(LED4_PIN, LOW);
    xTaskCreatePinnedToCore(
      Task1code, /* Task function. */
      "Task1",   /* name of task. */
      10000,     /* Stack size of task */
      NULL,      /* parameter of the task */
      1,         /* priority of the task */
      &Task1,    /* Task handle to keep track of created task */
      0);        /* pin task to core 0 */
  }

  delay(2000); // Đợi 2 giây trước khi đọc lại dữ liệu
}

void Task1code(void * pvParameters) {
  for (;;) {
    ulTaskNotifyTake(pdTRUE, portMAX_DELAY);
    while (windspeedHigh) {
      digitalWrite(LED4_PIN, HIGH);
      delay(500);
      digitalWrite(LED4_PIN, LOW);
      delay(500);
    }
  }
}
