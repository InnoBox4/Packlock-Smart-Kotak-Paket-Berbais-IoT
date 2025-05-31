<?php
// get_all_status.php
$conn = new mysqli("localhost", "root", "", "smart_locker");

$result = $conn->query("SELECT * FROM lockers");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
