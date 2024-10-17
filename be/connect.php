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
    // echo "<h1>ket noi csdl thanh cong</h1>"
?>