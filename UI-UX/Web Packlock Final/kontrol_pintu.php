<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Metode tidak diperbolehkan"]);
    exit;
}

$id = $_POST['id'] ?? null;
$aksi = $_POST['aksi'] ?? null;

if (!$id || !$aksi) {
    echo json_encode(["success" => false, "error" => "Parameter tidak lengkap"]);
    exit;
}

// URL API Python yang akan meneruskan perintah ke Arduino
$pythonApiUrl = "http://localhost:5000/send_command";

// Data yang akan dikirim ke Python server
$data = [
    'id' => $id,
    'aksi' => $aksi
];

// Inisialisasi cURL
$ch = curl_init($pythonApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

// Eksekusi request
$response = curl_exec($ch);

if ($response === false) {
    echo json_encode(["success" => false, "error" => "Gagal kirim ke Python server: " . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Kirim balik response dari Python ke client
echo $response;
