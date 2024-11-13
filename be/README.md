Đây là folder chứa code cho phần BE
API: Có 5 api
- POST /data_sensor_processing.php - Lấy dữ liệu cảm biến
- POST /action_history_processing.php - Lấy dữ liệu lịch sử hành động
- GET /getDeviceStatus.php - Lấy trạng thái bật/tắt cuối cùng của thiết bị trong csdl
- POST /updateDeviceAction.php - Gửi tín hiệu bật tắt đèn đến topic MQTT
- GET /getChartFromDB.php - Lấy 10 dữ liệu cảm biến cuối để vẽ biểu đồ

