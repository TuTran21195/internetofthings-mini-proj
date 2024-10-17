<?php
    header('Content-Type: application/json');
    // Kết nối cơ sở dữ liệu và hiển thị dữ liệu
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "iot_database";

    $conn = new mysqli($servername, $username, $password, $dbname);

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM tbl_data_sensor LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    $total_sql = "SELECT COUNT(*) as total FROM tbl_data_sensor";
    $total_result = $conn->query($total_sql);
    $total_rows = $total_result->fetch_assoc()['total'];

    $response = [
        'data' => $data,
        'total' => $total_rows,
        'page' => $page,
        'limit' => $limit
    ];

    echo json_encode($response);
?>