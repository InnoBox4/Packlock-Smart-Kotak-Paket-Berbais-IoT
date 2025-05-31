<?php
// Aktifkan laporan error untuk debugging (matikan ini di produksi)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tampilkan data GET yang masuk (debug)
echo "<pre>GET:\n";
print_r($_GET);
echo "</pre>";

// Cek parameter lengkap
if (
    isset($_GET["locker_number"]) &&
    isset($_GET["status_paket"]) &&
    isset($_GET["status_pintu_depan"]) &&
    isset($_GET["status_pintu_belakang"])
) {
    $locker_number = (int) $_GET["locker_number"];
    $status_paket = $_GET["status_paket"];
    $status_pintu_depan = $_GET["status_pintu_depan"];
    $status_pintu_belakang = $_GET["status_pintu_belakang"];

    // Koneksi ke database
    $conn = new mysqli("localhost", "root", "", "smart_locker");

    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // SQL update
    $sql = "UPDATE lockers SET 
                status_paket = ?,
                status_pintu_depan = ?,
                status_pintu_belakang = ?
            WHERE locker_number = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssi", $status_paket, $status_pintu_depan, $status_pintu_belakang, $locker_number);
        if ($stmt->execute()) {
            echo "Data berhasil diupdate";
        } else {
            echo "Gagal eksekusi query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Gagal prepare query: " . $conn->error;
    }

    $conn->close();
} else {
    echo "❌ Parameter tidak lengkap! Harus ada: locker_number, status_paket, status_pintu_depan, status_pintu_belakang.";
}
?>
