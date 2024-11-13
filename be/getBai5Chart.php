<?php

    header('Content-Type: application/json');
    // Kết nối cơ sở dữ liệu và hiển thị dữ liệu
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "iot_database";

    $conn = new mysqli($servername, $username, $password, $dbname);

    $sql = "SELECT * FROM (SELECT * FROM tbl_data_sensor ORDER BY id DESC LIMIT 10) sub ORDER BY id ASC";
    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $json_data = trim(json_encode($data));
    echo $json_data;
?>