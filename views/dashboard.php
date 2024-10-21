<!-- 
 light
humid
temperature
chart
remote board 
-->


<div class="container">
  <!-- Latest-measurement -->
  <div class="row">
    <div class="col">
      <div class="latest-measurement">
        <div class="circular-progress light">
          <div class="circular-progress__mask" id = "circular-progress__mask-bright"></div>
          <div class="circular-progress__inner" id = "latest_bright_bg"></div>
          <div class="circular-progress__inner_num" id = "latest_bright_num"></div>
        </div>
        <span class="text">Light (lux)</span>
      </div>
    </div>
    <div class="col">
      <div class="latest-measurement">
        <div class="circular-progress humid">
          <div class="circular-progress__mask" id = "circular-progress__mask-humid"></div>
          <div class="circular-progress__inner" id = "latest_humid_bg">
            <div class="circular-progress__inner_num" id = "latest_humid_num"></div>
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
            <div class="circular-progress__inner_num" id = "latest_tem_num"></div>
          </div>
        </div>
        <span class="text">Temperature (Celsius)</span>
      </div>
    </div>

    <div class="col remote-devices">
      BẢNG ĐIỀU KHIỂN THIẾT BỊ
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="led1" onclick="toggleDevice('led1')">
        <label class="form-check-label" for="led1">LED 1</label>
        <div id="led1_status" class="status-text">Loading...</div>
      </div>

      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="led2" onclick="toggleDevice('led2')">
        <label class="form-check-label" for="led2">LED 2</label>
        <div id="led2_status" class="status-text">Loading...</div>
      </div>

      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="led3" onclick="toggleDevice('led3')">
        <label class="form-check-label" for="led3">LED 3</label>
        <div id="led3_status" class="status-text">Loading...</div>
      </div>
    </div>

    <!-- CHART -->
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
