
//Khi load content cho web thì:
document.addEventListener('DOMContentLoaded', () => {
  if (currentPage === 'dashboard') {
    // console.log("this is Dashboard view")
    drawChart(); // Gọi hàm vẽ biểu đồ & các thông số sensor mới nhất nếu là dashboard
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
    const message = event.data;
    console.log(message)

    if (message === 'updatechart') {
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
    }else{
      console.error('Nhận được lệnh từ Websocket:', message);
    }

    // const newData = JSON.parse(event.data);
    // console.log('Message from WebSocket server:', newData);

    // if (newData.humidity && newData.temperature && newData.light) {
    //     // Nếu nhận được dữ liệu từ cảm biến, cập nhật biểu đồ
    //     console.log("Msg from sensor: ", newData);
    //     addNewDataToChart(newData);
    // } else if (newData.message) {
    //     // Nếu nhận được lệnh điều khiển LED hoặc phản hồi, hiển thị thông báo
    //     console.log("LED Message: ", newData.message);
    //     // Có thể cập nhật trạng thái LED trên giao diện, ví dụ: document.getElementById('led-status').innerText = newData.message;
    // }
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

function remoteDevices(){
  // Lắng nghe sự kiện click của nút switch
  const ledSwitch = document.getElementById('ledSwitch');
  ledSwitch.addEventListener('click', async () => {
      if (ledSwitch.checked) {
          // Nếu nút switch được bật(checked), thực hiện lưu vào cơ sở dữ liệu là on
          try {
              const response = await fetch('be/remoteDataSaveDB', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json'
                  },
                  body: JSON.stringify({
                      device: 'led',
                      action: 'off', // Trạng thái off
                      time: new Date().toISOString() // Thời gian hiện tại
                  })
              });
              if (response.ok) {
                  console.log('Dữ liệu đã được lưu vào cơ sở dữ liệu.');
              } else {
                  console.error('Lỗi khi lưu dữ liệu.');
              }
          } catch (error) {
              console.error('Lỗi kết nối đến máy chủ.');
          }
      }
  });
}

// ============================= DATA SENSOR VIEW ==============================

function getTableDataSensor(){
  console.log('123 this is one getTableDataSensor');
  // console.log(document.getElementById('datestart').value);
  
  $('#data-sensor-table').DataTable({
    "processing": true,
    "serverSide": true,
    "destroy": true, // Thêm dòng này để hủy bảng cũ trước khi tạo bảng mới
    "ajax": {
      "url": "be/data_sensor_processing.php",
      // "type": "POST",
      // "data": {
      //     "startDate": datestart,
      //     "endDate": dateEnd
      // }
    }
  });
}


function getTableDataSensorFilteringTime(){
  console.log('123 this is one');
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
  
  $('#action-history-table').DataTable({
    "processing": true,
    "serverSide": true,
    "destroy": true, // Thêm dòng này để hủy bảng cũ trước khi tạo bảng mới
    "ajax": {
      "url": "be/action_history_processing.php",
      // "type": "POST",
      // "data": {
      //     "startDate": datestart,
      //     "endDate": dateEnd
      // }
    }
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