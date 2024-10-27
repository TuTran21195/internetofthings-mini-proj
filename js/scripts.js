
//Khi load content cho web thì:
document.addEventListener('DOMContentLoaded', () => {
  if (currentPage === 'dashboard') {
    // console.log("this is Dashboard view")
    drawChart(); // Gọi hàm vẽ biểu đồ & các thông số sensor mới nhất nếu là dashboard
    getDeviceStatus(); // Lấy trạng thái bật/tắt mới nhất của thiết bị tr csdl
  } else if(currentPage === 'data-sensor' ) {
    getTableDataSensor();
  } else if (currentPage == 'action-history'){
    getTableActionHistory();
  }

});

// ===================================== DASHBOARD CODE ===============================================================================

// *** show nhiệt độ độ ẩm ánh sáng gần nhất****

  // chỉnh sửa cung tròn hiển thị phần trăm
function circularProgressMask(){
  let circularProgress__mask_bright = document.getElementById('circular-progress__mask-bright');
  let circularProgress__mask_humid = document.getElementById('circular-progress__mask-humid');
  let circularProgress__mask_tem = document.getElementById('circular-progress__mask-tem');

  let latest_bright_num = parseInt(document.getElementById('latest_bright_num').textContent, 10)/1000;
  circularProgress__mask_bright.style.background = `conic-gradient(rgba(0,0,0,0) ${latest_bright_num*360}deg, rgba(255, 255, 255, 0.87) 0deg)`

  let latest_humid_num = parseInt(document.getElementById('latest_humid_num').textContent, 10)/100;
  circularProgress__mask_humid.style.background = `conic-gradient(rgba(0,0,0,0) ${latest_humid_num*360}deg, rgba(255, 255, 255, 0.87) 0deg)`
  let latest_tem_num = parseInt(document.getElementById('latest_tem_num').textContent, 10)/100;
  circularProgress__mask_tem.style.background = `conic-gradient(rgba(0,0,0,0) ${latest_tem_num*360}deg, rgba(255, 255, 255, 0.87) 0deg)`

}


// <!-- Drawing chart & latest sensor in Dashboard-->
let chart;
function drawChart() {
  // console.log('start fetch')
  fetch('be/getChartFromDB.php')
  .then(response => response.json()) 
  .then(data  => {
      // console.log("Raw response:", data);
      if (data) {
        // Lấy dòng dữ liệu cuối cùng để đưa ra latestData 3 cái vòng tròn
        let latestData = data[data.length - 1];
          
        // Cập nhật giá trị cho các thẻ div
        document.getElementById("latest_bright_num").textContent = latestData.bright;
        document.getElementById("latest_humid_num").textContent = latestData.humid;
        document.getElementById("latest_tem_num").textContent = latestData.temperature;
        //vẽ lại cái vòng tròn
        circularProgressMask();

        //Bắt đầu vẽ chart
        let labels = data.map(item => item.time);
        let humidData = data.map(item => parseFloat(item.humid));
        let brightData = data.map(item => parseFloat(item.bright));
        let tempData = data.map(item => parseFloat(item.temperature));

        const dashboardChart = document.getElementById("chart-dashboard")
        // console.log(dashboardChart)
        chart = new Chart(dashboardChart, {
          type: "line",
          data: {
            labels: labels,
            datasets: [
              {
                label: "Nhiệt độ",
                backgroundColor: "red",
                borderColor: "red",
                data: tempData,
                tension: 0.4,
                yAxisID: 'humidmeasurement',
              },
              {
                label: "Độ ẩm",
                backgroundColor: "blue",
                borderColor: "blue",
                data: humidData,
                tension: 0.4,
                yAxisID: 'humidmeasurement',
              },
              {
                label: "Ánh sáng",
                backgroundColor: "yellow",
                borderColor: "yellow",
                data: brightData,
                tension: 0.4,
                yAxisID: 'brightmeasurement',
              },
            ],
          },
          options: {
            scales: {
              brightmeasurement: {
                beginAtZero: true,
                type: 'linear',
                position:'right'
              },
              humidmeasurement:{
                beginAtZero: true,
                type: 'linear',
                position:'left'
              }
            }
          }
        });

      } else {
          console.error('Empty response');
      }
  })
  .catch(error => console.error('Error:', error));
 
}

// WebSocket kết nối tới server WEBSOCKET KẾT NỐI TỚI SERVER
const ws = new WebSocket('ws://localhost:8081/ws');

ws.onopen = function() {
    console.log('WebSocket connection opened.');
};


ws.onclose = function() {
    console.log('WebSocket connection closed.');
};

ws.onmessage = function(event) { 
    // Nhận lệnh phản hồi từ Websocket
    // const message = event.data;
    // console.log(message)
    const message = JSON.parse(event.data);
    if (message.command === "updatechart")  {
      // Fetch new data and update chart
      fetch('be/getChartFromDB.php')
        .then(response => response.json())
        .then(data => {
          if (chart) {
            updateChartData(chart, data); // Update the chart with new data
          } else {
            console.error('Chart is not initialized.');
          }
        })
        .catch(error => console.error('Error fetching new data:', error));
    }
    else if (message.device) {
      // Xử lý phản hồi từ thiết bị (khi toggleDevice gửi lệnh)
      handleDeviceResponse(message);
    } 
    else{
      console.error('Nhận được lệnh từ Websocket:', message);
    }


};

// Function to update chart data
function updateChartData(chart, newData) {
  chart.data.labels = newData.map(item => item.time);
  chart.data.datasets[0].data = newData.map(item => parseFloat(item.temperature)); // Nhiệt độ
  chart.data.datasets[1].data = newData.map(item => parseFloat(item.humid));       // Độ ẩm
  chart.data.datasets[2].data = newData.map(item => parseFloat(item.bright));      // Ánh sáng
  chart.update(); // Update the chart

  // Lấy dòng dữ liệu cuối cùng để cập nhật latestData 3 cái vòng tròn
  let latestData = newData[newData.length - 1];
        
  // Cập nhật giá trị cho các thẻ div
  document.getElementById("latest_bright_num").textContent = latestData.bright;
  document.getElementById("latest_humid_num").textContent = latestData.humid;
  document.getElementById("latest_tem_num").textContent = latestData.temperature;
  //vẽ lại cái vòng tròn
  circularProgressMask();
}



// remote devices
function getDeviceStatus(){
  fetch('be/getDeviceStatus.php')
    .then(response => response.json())
    .then(data => {
      document.getElementById('led1').checked = data.led1 === 'on';
      document.getElementById('led2').checked = data.led2 === 'on';
      document.getElementById('led3').checked = data.led3 === 'on';
      // Hiển thị trạng thái thao tác với thiết bị {nó đang On hay OFF hoặc là Waiting, Loading, Failed to update}
      document.getElementById('led1_status').textContent = data.led1 === 'on' ? 'On' : 'Off';
      document.getElementById('led2_status').textContent = data.led2 === 'on' ? 'On' : 'Off';
      document.getElementById('led3_status').textContent = data.led3 === 'on' ? 'On' : 'Off';
    })
    .catch(error => console.error('Error fetching device status:', error));
}

let timeoutId; 

// Hàm xử lý sau khi nhận đc phản hồi từ thiết bị HW via Websocket
function handleDeviceResponse(message) {
  const device = message.device; // Lấy tên thiết bị
  const action = message.status.includes('on') ? 'on' : 'off'; // Xác định trạng thái bật/tắt
  
  const switchElement = document.getElementById(device);
  const statusElement = document.getElementById(device + '_status');

  if (message.status === `${action} success`) {
    clearTimeout(timeoutId); // Hủy bỏ timeout khi nhận được phản hồi

    // Cập nhật trạng thái switch sau khi phản hồi thành công
    switchElement.checked = !switchElement.checked; // Đưa hình ảnh cửa switch nó sang trạng thái mới
    switchElement.disabled = false; // Cho phép người dùng thao tác lại
    statusElement.textContent = action === 'on' ? 'On' : 'Off'; // Cập nhật trạng thái trên giao diện
    console.log(`${device} đã ${action} thành công`);
  }
}

//Hàm xử lý sau khi click vào switch bật tắt thiết bị trên giao diện dashboard
function toggleDevice(device) { 
  const switchElement = document.getElementById(device); // Lấy switch theo id (device)
  const statusElement = document.getElementById(device + '_status'); // Lấy phần tử hiển thị trạng thái

  const action = switchElement.checked ? 'on' : 'off'; // Nếu switch đang checked thì là 'on', ngược lại là 'off'
  switchElement.checked = !switchElement.checked; // Trả lại trạng thái trước đó của switch
  // Disable the switch và hiển thị trạng thái 'Waiting...'
  switchElement.disabled = true;
  statusElement.textContent = 'Waiting...';


  // Gửi yêu cầu bật/tắt tới backend
  fetch('be/updateDeviceAction.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ device: device, action: action }) // Gửi device và action dưới dạng JSON
  })
  .then(response => response.json())
  .then(data => { 
    if (data.success) { // Nếu yêu cầu gửi đến BE thành công
      console.log("Đã gửi tín hiệu bật/tắt đến BE. Đang chờ phản hồi...");

      // Đặt thời gian timeout (60 giây) nếu không nhận phản hồi từ WebSocket
      timeoutId = setTimeout(() => {
        switchElement.disabled = false; // Re-enable switch để người dùng có thể thao tác lại
        statusElement.textContent = "Error: Timeout!"; // Hiển thị lỗi
        console.error("Không nhận được phản hồi từ server trong thời gian cho phép.");
      }, 300000); // 300 giây timeout (~ 5 phút)


    } else {
      // Trong trường hợp lỗi khi gửi yêu cầu đến BE
      statusElement.textContent = 'Failed to update'; // Hiển thị lỗi
      switchElement.disabled = false; // Cho phép người dùng thao tác lại
    }
  })
  .catch(error => {
    // Xử lý lỗi khi gửi yêu cầu
    console.error('Lỗi khi gửi yêu cầu đến backend:', error);
    statusElement.textContent = 'Error: Request failed'; // Hiển thị lỗi
    switchElement.disabled = false; // Cho phép người dùng thao tác lại
  });
}



// ============================= DATA SENSOR VIEW ==============================

function getTableDataSensor(){
  console.log('this is one getTableDataSensor');
  // console.log(document.getElementById('datestart').value);
  console.log( $('#searchColumn').val());
  
  const table = $('#data-sensor-table').DataTable({
    "processing": true,
    "serverSide": true,
    "destroy": true, // Thêm dòng này để hủy bảng cũ trước khi tạo bảng mới
    "ajax": {
      "url": "be/data_sensor_processing.php",
      "type": "POST",
      "data": function(d) {
          d.searchColumn = $('#searchColumn').val();
      },
      "dataSrc": function(json) {
                // Xử lý trường bổ sung nếu cần
                console.log("Extra Info: ", json.extraInfo);
                return json.data;
            }
    },
    language: {
      search: "Tìm kiếm BE:"
    },
  });

  // Sự kiện thay đổi cho dropdown
  $('#searchColumn').change(function() {
    console.log('Search column changed to: ' + $(this).val());
    table.ajax.reload();
  });
}


function getTableDataSensorFilteringTime(){
  console.log('123 this is getTableDataSensorFilteringTime');
  console.log(convertFlatpickrTime(document.getElementById('datestart').value));
  
  // var startDate = $('#startDate').val();
  // var endDate = $('#endDate').val();

  var startDate = convertFlatpickrTime(document.getElementById('datestart').value)
  var endDate = convertFlatpickrTime(document.getElementById('dateEnd').value)

  console.log("ngay bat dau & ket thuc data sensor: ", startDate, endDate);
  
  $('#data-sensor-table').DataTable({
    "processing": true,
    "serverSide": true,
    "destroy": true, // Thêm dòng này để hủy bảng cũ trước khi tạo bảng mới
    "ajax": {
      "url": "be/data_sensor_processing.php",
      "type": "POST",
      "data": {
          "startDate": startDate,
          "endDate": endDate
      }
    }
  });
  console.log("xem bang da thay doi chua");
  
}

// function convertFlatpickrTime(flatpickrTime) {
//   const parsedTime = new Date(flatpickrTime);
//   const formattedTime = parsedTime.toISOString().slice(0, 19).replace("T", " ");
//   return formattedTime;
// }
function convertFlatpickrTime(flatpickrTime) {
  const formattedTime = flatpickrTime.replace("T", " ") + ":00";
  return formattedTime;
}


// ============================= ACTION HISTORY VIEW ==============================

function getTableActionHistory(){
  console.log('123 this is action history');
  // console.log(document.getElementById('datestart').value);
  console.log( $('#searchColumn').val());
  
  $('#action-history-table').DataTable({
    "processing": true,
    "serverSide": true,
    "destroy": true, // Thêm dòng này để hủy bảng cũ trước khi tạo bảng mới
    "ajax": {
      "url": "be/action_history_processing.php",
      "type": "POST",
      "data": function(d) {
          d.searchColumn = $('#searchColumn').val();
      },
      "dataSrc": function(json) {
                // Xử lý trường bổ sung nếu cần
                console.log("Extra Info: ", json.extraInfo);
                return json.data;
            }
    },
    language: {
      search: "Tìm kiếm BE:"
    },
  });

  // Sự kiện thay đổi cho dropdown
  $('#searchColumn').change(function() {
    console.log('Search column changed to: ' + $(this).val());
    table.ajax.reload();
  });
}

function getTableActionHistoryFilteringTime(){
  console.log('123 this is one');
  console.log(convertFlatpickrTime(document.getElementById('datestart').value));
  
  // var startDate = $('#startDate').val();
  // var endDate = $('#endDate').val();

  var startDate = convertFlatpickrTime(document.getElementById('datestart').value)
  var endDate = convertFlatpickrTime(document.getElementById('dateEnd').value)

  console.log(startDate, endDate);
  
  $('#action-history-table').DataTable({
    "processing": true,
    "serverSide": true,
    "destroy": true, // Thêm dòng này để hủy bảng cũ trước khi tạo bảng mới
    "ajax": {
      "url": "be/action_history_processing.php",
      "type": "POST",
      "data": {
          "startDate": startDate,
          "endDate": endDate
      }
    }
  });
}