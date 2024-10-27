<h1 class="text-center" style="margin: 30px">Welcome to the Action History</h1>

<!-- - Tìm kiếm: phải tìm ở BE chứ ko đc tìm trên FE (có 1 cái sellect a choice để tìm kiếm theo nhiệt độ hoặc độ ẩm hay ánh sáng)
- Sắp xếp thì cũng phải làm ở BE chứ ko đc sxep trên FE (sắp xếp ở các cột ấy)
- Filter theo trong khoảng tgian
- Phân trang: page & pageLimit -->

<div class="container-fluid">
<div class="row" style="margin: 2vw">
    <div class="col-md-4">
    <label for="searchColumn">Tìm kiếm theo:</label>
      <select class="form-select" id="searchColumn" aria-label="Default select example">
        <!-- <option selected>Chọn tìm kiếm theo: Any</option> -->
        <option value="">---</option>
        <option value="devices">Tìm theo Thiết bị</option>
        <option value="action">Tìm theo Action</option>
      </select>
    </div>
  </div>

  <div class="row" style="margin: 0 2vw 2vw 2vw">
    <!-- <div class="col">
      <form>
        <p>Chọn thời gian bắt đầu</p>
        <input id="datestart" type="datetime-local" placeholder="Chọn thời điểm bắt đầu" />
      </form>
    </div>
    <div class="col">
      <form>
        <p>Chọn thời gian kết thúc</p>
        <input id="dateEnd" type="datetime-local" placeholder="Chọn thời điểm kết thúc" />
      </form>
    </div>
    <div class="col">
    <button type="button" class="btn btn-success" onclick="getTableActionHistoryFilteringTime()">Filter</button>
    </div>
    <div class="col">
    <button type="button" class="btn btn-success" onclick="getTableActionHistory()">Clear all Filter</button>
    </div> -->

  </div>

  <table class="table table-bordered table-hover" id = "action-history-table">
    <thead>
      <tr class="table-primary">
        <th scope="col">#</th>
        <th scope="col">Devices</th>
        <th scope="col">Action</th>
        <th scope="col">Time</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
</div>