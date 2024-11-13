<!-- 
 light
humid
temperature
chart
remote board 
-->


<div class="container">
    <div class="row">
      <!-- Latest-measurement -->
    <div class="col">
      <div class="latest-measurement">
        <div class="circular-progress wind">
          <div class="circular-progress__mask" id = "circular-progress__mask-wind"></div>
          <div class="circular-progress__inner" id = "latest_wind_bg"></div>
          <div class="circular-progress__inner_num" id = "latest_wind_num"></div>
        </div>
        <span class="text">WindSpeed (m/s)</span>
      </div>
    </div>
     <!-- CHART -->
    <div class="col">
        <div style="width: 100%; height: 350px">
          <canvas id="chart-bai5"></canvas>
        </div>
        <div id="waringDiv" class="col waring-form" style="width: 100%;">
          <div class = "icon">
            <svg class="waring-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
            <path fill="currentColor" d="M569.52 440L327.52 40c-18.3-29.9-59.74-29.9-78.06 0L6.48 440c-18.28 29.92 4.56 72 39.03 72h484.98c34.46 0 57.29-42.08 39.03-72zm-281.52-40c-13.25 0-24-10.75-24-24s10.75-24 24-24 24 10.75 24 24-10.75 24-24 24zm24-96c0 13.25-10.75 24-24 24s-24-10.75-24-24V184c0-13.25 10.75-24 24-24s24 10.75 24 24v120z"/>
            </svg>
            <div>Warning!!!</div>
          </div>
        </div>
    </div>

    <!-- CHART -->
    <!-- <div class="row">
      <div class="col">
        <div style="width: 100%; height: 500px">
          <canvas id="chart-bai5"></canvas>
        </div>
      </div>
    </div> -->
  </div>
</div>

<script>
  console.log("this is bai5")
</script>
