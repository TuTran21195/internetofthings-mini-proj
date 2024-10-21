<?php
// jQuery DataTable (Server Side) Searching, Sorting, Filtering, and Pagination


    header('Content-Type: application/json');
    // Kết nối cơ sở dữ liệu và hiển thị dữ liệu
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "iot_database";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Xử lý yêu cầu
    $request = $_REQUEST;
    $columns = array(
        0 => 'id',
        1 => 'devices',
        2 => 'action',
        3 => 'time'
    );

    $sql = "SELECT * FROM tbl_action_history";
    $query = $conn->query($sql);
    $totalData = $query->num_rows;
    $totalFiltered = $totalData;

    $sql = "SELECT * FROM tbl_action_history WHERE 1=1";
    // lọc trong khoảng thời gian
    if (!empty($request['startDate']) && !empty($request['endDate'])) {
        $startDate = $request['startDate'];
        $endDate = $request['endDate'];
        $sql .= " AND time BETWEEN '$startDate' AND '$endDate'";
    }

    // tìm kiếm
    if (!empty($request['search']['value'])) {
        $sql .= " AND (devices LIKE '%" . $request['search']['value'] . "%' ";
        $sql .= " OR action LIKE '%" . $request['search']['value'] . "%' ";
        $sql .= " OR time LIKE '%" . $request['search']['value'] . "%' )";
    }
    $query = $conn->query($sql);
    $totalFiltered = $query->num_rows;

    $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . " LIMIT " . $request['start'] . " ," . $request['length'] . " ";
    $query = $conn->query($sql);

    $data = array();
    while ($row = $query->fetch_assoc()) {
        $nestedData = array();
        $nestedData[] = $row["id"];
        $nestedData[] = $row["devices"];
        $nestedData[] = $row["action"];
        // $nestedData[] = $row["time"]; 
        $nestedData[] = date('d-m-Y H:i:s', strtotime($row["time"])); // đưa thời gian về dạng ngày-tháng-năm
        $data[] = $nestedData;
    }

    $json_data = array(
        "draw" => intval($request['draw']),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data
    );

    echo json_encode($json_data);
    
?>