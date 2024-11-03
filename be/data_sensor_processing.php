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

    $ordercolumn = $request['order'][0]['column'];
    $orderDir =  $request['order'][0]['dir']; 
    $reqs = $request['start']; 
    $reqlen = $request['length']; 

    $searchColumn= $request['searchColumn']; 
    $columns = array(
        0 => 'id',
        1 => 'humid',
        2 => 'bright',
        3 => 'temperature',
        4 => 'time'
    );

    $sql = "SELECT * FROM tbl_data_sensor";
    $query = $conn->query($sql);
    $totalData = $query->num_rows;
    $totalFiltered = $totalData;


    $sql = "SELECT * FROM tbl_data_sensor WHERE 1=1";

    // // lọc trong khoảng thời gian
    // if (!empty($request['startDate']) && !empty($request['endDate'])) {
    //     $startDate = $request['startDate'];
    //     $endDate = $request['endDate'];
        // $sql .= " AND time BETWEEN '$startDate' AND '$endDate'";
    // }

    // tìm kiếm
    // if (!empty($request['search']['value'])) {
    //     $sql .= " AND (humid LIKE '%" . $request['search']['value'] . "%' ";
    //     $sql .= " OR bright LIKE '%" . $request['search']['value'] . "%' ";
    //     $sql .= " OR temperature LIKE '%" . $request['search']['value'] . "%' ";
    //     $sql .= " OR time LIKE '%" . $request['search']['value'] . "%' )";
    // }
    if (!empty($request['search']['value']) && !empty($request['searchColumn'])) {
        $sql .= " AND $searchColumn LIKE '%" . $request['search']['value'] . "%' ";
    }
    $query = $conn->query($sql);
    $totalFiltered = $query->num_rows;

    $sql .= " ORDER BY " . $columns[$ordercolumn] . " " . $orderDir . " LIMIT " . $request['start'] . " ," . $request['length'] . " ";
    // $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . " LIMIT " . $request['start'] . " ," . $request['length'] . " ";
    $query = $conn->query($sql);

    $data = array();
    while ($row = $query->fetch_assoc()) {
        $nestedData = array();
        $nestedData[] = $row["id"];
        $nestedData[] = $row["humid"];
        $nestedData[] = $row["bright"];
        $nestedData[] = $row["temperature"];
        $nestedData[] = $row["time"]; 
        // $nestedData[] = date('d-m-Y H:i:s', strtotime($row["time"])); // đưa thời gian về dạng ngày-tháng-năm
        $data[] = $nestedData;
    }

    $ordercolumn = $request['order'][0]['column'];
    $orderDir =  $request['order'][0]['dir']; 
    $reqs = $request['start']; 
    $reqlen = $request['length']; 
    // Thêm trường bổ sung vào mảng JSON
    $additionalInfo =" toi nhan duoc $searchColumn, $ordercolumn, $orderDir, $reqs, $reqlen ";

    $json_data = array(
        "draw" => intval($request['draw']),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data,
        "extraInfo" => $additionalInfo
    );

    echo json_encode($json_data);

    // echo '<pre>';
    // print_r($request['order']);
    // echo '</pre>';
    // die();
?>