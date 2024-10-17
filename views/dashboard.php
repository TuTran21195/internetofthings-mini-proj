<!-- 
 light
humid
temperature
chart
remote board 
-->

<?php
  
    // Kết nối cơ sở dữ liệu và hiển thị dữ liệu
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "iot_database";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Kết nối csdl thất bại: " . $conn->connect_error);
    }

$sql = "SELECT * FROM tbl_data_sensor ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

$latest_bright = -1;
$latest_humid = -1;
$latest_tem = -1;


if ($result->num_rows > 0) {
    // Lấy dữ liệu dòng cuối cùng
    $row = $result->fetch_assoc();
    
    $latest_humid = $row['humid'];
    $latest_bright = $row['bright'];
    $latest_tem = $row['temperature'];
    // echo "$latest_bright $latest_humid $latest_tem";
} 

// remote devices

?>


<div class="container">
  <!-- Latest-measurement -->
  <div class="row">
    <div class="col">
      <div class="latest-measurement">
        <div class="circular-progress light">
          <div class="circular-progress__mask" id = "circular-progress__mask-bright"></div>
          <div class="circular-progress__inner" id = "latest_bright_bg"></div>
          <div class="circular-progress__inner_num" id = "latest_bright_num"><?php echo $latest_bright ?></div>
        </div>
        <span class="text">Light (lux)</span>
      </div>
    </div>
    <div class="col">
      <div class="latest-measurement">
        <div class="circular-progress humid">
          <div class="circular-progress__mask" id = "circular-progress__mask-humid"></div>
          <div class="circular-progress__inner" id = "latest_humid_bg">
            <div class="circular-progress__inner_num" id = "latest_humid_num"><?php echo $latest_humid ?></div>
          </div>
        </div>
        <span class="text">Humid (%)</span>
      </div>
    </div>
    <div class="col">
      <div class="latest-measurement">
        <div class="circular-progress tem">
          <div class="circular-progress__mask" id = "circular-progress__mask-tem" ></div>
          <div class="circular-progress__inner" id = "latest_tem_bg">
            <div class="circular-progress__inner_num" id = "latest_tem_num"><?php echo $latest_tem ?></div>
          </div>
        </div>
        <span class="text">Temperature (Celsius)</span>
      </div>
    </div>

    <div class="col remote-devices">
      BẢNG ĐIỀU KHIỂN THIẾT BỊ
      <div class="form-check form-switch">
        <?php 
          // Lấy thông tin trạng thái của thiết bị đèn LED
          $sql = "SELECT action FROM tbl_action_history WHERE devices = 'led' ORDER BY id DESC LIMIT 1";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $ledStatus = $row['action'];

              // Hiển thị nút switch đã kiểm tra (checked) nếu trạng thái là "bật"
              if ($ledStatus === 'on') {
                  echo '<input class="form-check-input" id="led" type="checkbox" role="switch" checked/>';
              } else {
                echo '<input class="form-check-input" id="led" type="checkbox" role="switch"/>';
              }
          } else {
              // Thiết bị không tồn tại trong cơ sở dữ liệu
              echo '<input class="form-check-input" id="led" type="checkbox" role="switch"/>';
          }
        ?>
        <label class="form-check-label" for="flexSwitchCheckDefault">Đèn (led)</label>
      </div>
      <div class="form-check form-switch">
      <?php 
          // Lấy thông tin trạng thái của thiết bị đèn LED
          $sql = "SELECT action FROM tbl_action_history WHERE devices = 'humidifier' ORDER BY id DESC LIMIT 1";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $ledStatus = $row['action'];

              // Hiển thị nút switch đã kiểm tra (checked) nếu trạng thái là "bật"
              if ($ledStatus === 'on') {
                  echo '<input class="form-check-input" id="humid" type="checkbox" role="switch" checked/>';
              } else {
                echo '<input class="form-check-input" id="humid" type="checkbox" role="switch"/>';
            }
          } else {
              // Thiết bị không tồn tại trong cơ sở dữ liệu
              echo '<input class="form-check-input" id="humid" type="checkbox" role="switch"/>';
          }
        ?>
        <label class="form-check-label" for="flexSwitchCheckChecked">Máy phun sương (Air humidifier)</label>
      </div> 
      <div class="form-check form-switch">
      <!-- <i class="fa-solid fa-fan"></i> -->

        <?php 
          // Lấy thông tin trạng thái của thiết bị đèn LED
          $sql = "SELECT action FROM tbl_action_history WHERE devices = 'fan' ORDER BY id DESC LIMIT 1";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $ledStatus = $row['action'];

              // Hiển thị nút switch đã kiểm tra (checked) nếu trạng thái là "bật"
              if ($ledStatus === 'on') {
                  echo '<input class="form-check-input" id="fan" type="checkbox" role="switch" checked/>';
              } else {
                echo '<input class="form-check-input" id="fan" type="checkbox" role="switch"/>';
              }
          } else {
              // Thiết bị chưa tồn tại trong cơ sở dữ liệu
              echo '<input class="form-check-input" id="fan" type="checkbox" role="switch"/>';
          }
        ?>
        <label class="form-check-label" for="flexSwitchCheckChecked">Quạt (fan)</label>
      </div>
      <!-- <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDisabled" disabled />
          <label class="form-check-label" for="flexSwitchCheckDisabled">Disabled switch checkbox input</label>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckCheckedDisabled" checked disabled />
          <label class="form-check-label" for="flexSwitchCheckCheckedDisabled">Disabled checked switch checkbox input</label>
        </div> -->
    </div>

    <!-- CHART & remote -->
    <div class="row">
      <div class="col">
        <div style="width: 100%; height: 500px">
          <canvas id="chart-dashboard"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  console.log("this is dash")
</script>
