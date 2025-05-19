<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Packlock Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
        <h1>Packlock</h1>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <input type="text" placeholder="Search">
            <span>👤 John Doe</span>
        </div>

        <h2>Dashboard</h2>
        <div class="stats">
            <?php
                $total = $conn->query("SELECT COUNT(*) AS total FROM lockers")->fetch_assoc()['total'];
                $available = $conn->query("SELECT COUNT(*) AS available FROM lockers WHERE status = 'Available'")->fetch_assoc()['available'];
                $inuse = $total - $available;
            ?>
            <div class="card">🔓 <?= $available ?> <p>Open Lockers</p></div>
            <div class="card">⏱️ <?= $total ?> <p>Total Usage</p></div>
            <div class="card">🗄️ <?= $inuse ?> <p>Occupied Lockers</p></div>
        </div>

        <div class="lockers">
            <h3>Lockers</h3>
            <table>
                <tr><th>Lockers</th><th>Status</th></tr>
                <?php
                    $result = $conn->query("SELECT * FROM lockers");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['locker_name']}</td>
                            <td><span class='status {$row['status']}'>{$row['status']}</span></td>
                        </tr>";
                    }
                ?>
            </table>
        </div>

        <div class="action">
            <button onclick="alert('Membuka loker')">🔓 Buka</button>
        </div>
    </div>
</body>
</html>
