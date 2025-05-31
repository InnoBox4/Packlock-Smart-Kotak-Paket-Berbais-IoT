<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Metode harus POST']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$aksi = $_POST['aksi'] ?? 'reset_paket'; // default perintahnya reset_paket

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID tidak valid']);
    exit;
}

// Koneksi ke database
$host = "localhost";
$dbname = "smart_locker";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit;
}

// Update status_paket di database
$sql = "UPDATE lockers SET status_paket = 'Tidak Ada' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Kirim perintah ke Python server
    $pythonApiUrl = 'http://localhost:5000/send_command';
    $postData = http_build_query([
        'id' => $id,
        'aksi' => $aksi
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $postData
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($pythonApiUrl, false, $context);

    if ($result === false) {
        echo json_encode(['success' => false, 'error' => 'Gagal mengirim ke server Python']);
    } else {
        $response = json_decode($result, true);
        echo json_encode([
            'success' => true,
            'python_response' => $response
        ]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal update database: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
