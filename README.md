# Iotminiproj
@Author: Đoàn Thị Trà My - B21DCAT134 :blush:
@Môn học: IoT và ứng dụng
@Nhóm lớp: 05
@GVHD: Nguyễn Quốc Uy
Đề tài:  **Hệ thống đo nhiệt độ, độ ẩm, ánh sáng \& điều khiển thiết bị** 

# Giới thiệu
Mục tiêu là xây dựng một web đơn giản để nhận thông tin nhiệt độ, độ ẩm ánh sáng và điều khiển ngược lại thiết bị (bật/tắt đèn)
Để xây dựng một hệ thống IoT hiệu quả và chi phí thấp, bài tập này sử dụng ESP32/ESP8266 làm nền tảng chính. Các module này sở hữu nhiều ưu điểm như kích thước nhỏ gọn, tích hợp Wi-Fi, nhiều chân GPIO, dễ lập trình, và giá thành phải chăng.


# Thiết bị cần mua
 - Thiết bị chính (bắt buộc phải có):
   - ESP32 hoặc ESP8266 (trong bài là ESP32) $\approx$ 80k
   - Cảm biến:
     - DHT11 hoặc DHT22: đo nhiệt độ và độ ẩm: $\approx$ 10k 
     - Cảm biến quang trở: đo ánh sáng (trong bài sử dụng cảm biến ánh sáng 2 đầu ra: 10k)
 - Các thiết bị khác (có thể thay thế thành các loại khác nhau - một số cái có thể ko mua)
   - breadboard: ổ cắm (có thể mua loại nhỏ - loại dài, để dễ thì cắm một nửa con ESP thôi không cần cắm cả)
   - Dây nối (nên mua khoảng 10 dây đực-đực và đi xin thêm 1 dây đực-cái hoặc cái-cái (nhưng nếu không có thì có thể hàn chân cũng ok))
   - Đèn led: Mua loại có số vôn tùy thích nhưng không nên mua loại < 3V vì nguồn ra của chân ESP32 là 3V.
   

# Thiết kế hệ thống
## Các tính năng của hệ thống:
- Ứng dụng theo dõi thông số trong phòng cho biết: nhiệt độ, độ ẩm, ánh sáng, độ bụi, mưa, khí gas,... thời gian thực đang như thế nào.
- Bật tắt đèn, điều hòa, quạt,… thông qua ứng dụng web.

## Các giao diện gồm có:
  - Trang Dashboard:
    - Gồm có thông số hiện tại của môi trường do cảm biến gửi về
    - Một biểu đồ line thể hiện 10 bộ giá trị thông số môi trường gần đây nhất
    - Một bảng điều khiển thiết bị (bảng bật tắt đèn)
  - Trang Data sensor: Chứa bảng lịch sử các giá trị cảm biến được lưu lại trong CSDL
  - Trang Action History: Chứa bảng lịch sử các thao tác bật tắt thiết bị trong CSDL (chỉ có thao tác thành công thì mới được lưu vào CSDL)
  - Trang Profile: Chứa thông tin của sinh viên thực hiện.

## Sơ đồ thiết kế chi tiết hệ thống - Luồng thực hiện - Cơ sở dữ liệu - API doc: 
Xem ảnh sơ đồ thiết kế chi tiết hệ thống & luồng thực hiện cũng như các bảng CSDL trong file [báo cáo pdf](./Bao_cao_BTL_IoT_va_ung_dung_Latex.pdf). 

Luồng: Có 2 luồng chính
- Luồng gửi dữ liệu từ Hardware $\rightarrow$ BE & BE lưu vào CSDL $\rightarrow$ FE (Để trao đổi với nhau thì HW và BE gửi data thông qua MQTT broker trên topic MQTT)
- Luồng gửi tín hiệu điều khiển từ FE $\rightarrow$ BE $\rightarrow$ Hardware xử lý $\rightarrow$ gửi tín hiệu bật tắt thành công đến BE $\rightarrow$ BE lưu vào CSDL  $\rightarrow$ FE

Cơ sở dữ liệu: Có 2 table
  - tbl_data_sensor: id, humid, bright, temperature, time.
  - tbl_action_history id, devices, action, time.

API: Có 5 api
- POST /data_sensor_processing.php - Lấy dữ liệu cảm biến
- POST /action_history_processing.php - Lấy dữ liệu lịch sử hành động
- GET /getDeviceStatus.php - Lấy trạng thái bật/tắt cuối cùng của thiết bị trong csdl
- POST /updateDeviceAction.php - Gửi tín hiệu bật tắt đèn đến topic MQTT
- GET /getChartFromDB.php - Lấy 10 dữ liệu cảm biến cuối để vẽ biểu đồ

# Cấu hình chạy proj
## Các nhiệm vụ:
- Giao diện: code 4 trang giao diện như đã mô tả ở trên.
- Kết nối giao diện với CSDL: Hiển thị lên được các thông số mẫu trong CSDL lên giao diện. Yêu cầu:
  - Các bảng thì phải được phân trang
  - Tìm kiếm thì phải tìm kiếm phía BE chứ không được tìm kiếm phía FE
  - Tìm kiếm theo tgian thì phải là LIKE "%searchTime%".
- MQTT broker: Thực hiện pub, sub thành công lên topic giữa các terminal. Yêu cầu:
    - Đổi cổng mặc định (trong bài đổi cổng thành 2003)
    - Đặt username & password (không có thì ko có điểm)
- Lắp đặt phần cứng & Gửi data từ phần cứng lên topic MQTT.
- Lấy data trên topic MQTT lưu vào CSDL
- Xử lý việc gửi tín hiệu điều khiển từ web

## Quy trình
### Các thành phần code:
- folder `be` chứa các file code phía Backend
- folder `vendor` chứa các thư viện được tải về thông qua Composer như phpMQTT, ratchet (để code Websocket),...
- folder còn lại là các file code phía FE.
- Mẫu cơ sở dữ liệu nằm ở file `iot_database_sample.sql`
- API doc theo Swagger được ghi tại file `openapi.json`

### Cách implement:


# Kết quả

