<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Packlock - Kotak Paket Pintar</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="sidebar">
    <h1>Packlock</h1>
  </div>

  <div class="main-content">
    <div class="top-bar">
    </div>

    <div class="stats" id="stats-box">
      <!-- Diisi oleh JS -->
    </div>

    <div class="lockers">
      <table>
        <thead>
          <tr>
            <th>Locker</th>
            <th>Status Paket</th>
            <th>Pintu Depan</th>
            <th>Terakhir Update</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="locker-body">
          <!-- Diisi oleh JS -->
        </tbody>
      </table>
    </div>

    <div class="action">
      <button onclick="fetchData()">🔃 Refresh</button>
    </div>
  </div>

  <script>
    function fetchData() {
      fetch('get_all_status.php')
        .then(res => res.json())
        .then(data => {
          const body = document.getElementById("locker-body");
          const statsBox = document.getElementById("stats-box");
          body.innerHTML = "";

          let tidakadapaket = 0, adapaket = 0;
          data.forEach(locker => {
            if (locker.status_paket === "Ada") adapaket++;
            else tidakadapaket++;

            const row = `
              <tr data-id="${locker.id}">
                <td>${locker.locker_name}</td>
                <td><span class="status ${locker.status_paket === 'Ada' ? 'status-ada' : 'status-tidak'}">${locker.status_paket}</span></td>
                <td><span class="status ${locker.status_pintu_depan === 'Terbuka' ? 'status-buka' : 'status-tutup'}">${locker.status_pintu_depan}</span></td>
                <td>${locker.waktu}</td>
                <td>
                  <div class="control-group">
                    <button class="save-btn" onclick="saveName(${locker.id}, this)">💾 Simpan</button>
                    <button class="control-btn" onclick="controlDoor(${locker.id}, 'buka')">🔓 Buka</button>
                    <button class="control-btn" onclick="controlDoor(${locker.id}, 'tutup')">🔓 Tutup</button>
                    <button class="control-btn" onclick="resetPaket(${locker.id}, this)">♻️ Reset Paket</button>
                  </div>
                </td>
              </tr>
            `;
            body.innerHTML += row;
          });

          statsBox.innerHTML = `
            <div class="card">🔓 ${tidakadapaket} <p>Locker Tidak Ada Paket</p></div>
            <div class="card">📦 ${adapaket} <p>Locker Ada Paket</p></div>
            <div class="card">📊 ${data.length} <p>Total Locker</p></div>
          `;
        });
    }

    function saveName(id, btn) {
      const row = btn.closest('tr');
      const nama = row.querySelector('.editable-nama').innerText.trim();

      fetch('update_user.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&nama_pengguna=${encodeURIComponent(nama)}`
      }).then(() => alert("Nama pengguna disimpan!"));
    }

    function controlDoor(id, action) {
      fetch('kontrol_pintu.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&aksi=${action}`
      })
      .then(res => res.json())  // pastikan PHP mengembalikan JSON
      .then(data => {
        if (data.success) {
          alert("Perintah berhasil dikirim!");
          fetchData();
        } else {
          alert("Gagal: " + data.error);
        }
      })
      .catch(err => alert("Error: " + err));
    }


    function resetPaket(id) {
    fetch('reset_paket.php', {
    method: 'POST',
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}`
  })
  .then(res => res.json())
  .then(result => {
    if (result.success) {
      alert("Status paket berhasil di-reset!");
      fetchData(); // refresh data setelah reset
    } else {
      alert("Gagal reset status paket: " + result.error);
    }
  })
  .catch(err => alert("Error: " + err));
}

    fetchData();
    setInterval(fetchData, 1000); // auto-refresh
  </script>
</body>
</html>
