<?php
// Fetch the latest status from the database
$pdo = new PDO("mysql:host=localhost;dbname=iot_database", "root", "");

$stmt = $pdo->query("SELECT devices, `action` FROM tbl_action_history WHERE id IN ( SELECT MAX(id) FROM tbl_action_history GROUP BY devices)");

$deviceStatus = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $deviceStatus[$row['devices']] = $row['action'];
}

echo json_encode($deviceStatus);
?>
