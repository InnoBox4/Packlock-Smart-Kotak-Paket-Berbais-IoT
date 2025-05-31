import threading
import time
from flask import Flask, request, jsonify
import serial
import requests

app = Flask(__name__)

# Buka serial port Arduino, sesuaikan COM port dan baudrate
try:
    ser = serial.Serial('COM7', 9600, timeout=1)
except serial.SerialException as e:
    print(f"Gagal buka serial port: {e}")
    exit(1)

# Variabel global untuk menyimpan status yang diterima dari Arduino
status_data = {
    "status_paket": None,
    "status_pintu_depan": None,
}

locker_number = 1  # Contoh nomor locker, bisa disesuaikan

# Fungsi background untuk baca data dari Arduino terus menerus
def baca_serial():
    global status_data
    while True:
        try:
            line = ser.readline().decode().strip()
            if line:
                print("[Dari Arduino] >>", line)
                # Parsing format "key=value"
                if "=" in line:
                    key, value = line.split("=", 1)
                    if key in status_data:
                        status_data[key] = value

                # Kalau sudah lengkap data, bisa langsung kirim ke web server lain
                if all(status_data.values()):
                    params = {
                        "locker_number": locker_number,
                        **status_data
                    }
                    print("[Ke PHP] >>", params)
                    try:
                        requests.get("http://localhost/Packlock/update_status.php", params=params, timeout=1)
                    except Exception as e:
                        print(f"Gagal kirim ke PHP: {e}")

        except Exception as e:
            print(f"Error baca serial: {e}")
            time.sleep(1)

# Mulai thread baca serial
threading.Thread(target=baca_serial, daemon=True).start()

# Endpoint Flask untuk menerima perintah dari web dan kirim ke Arduino
@app.route('/send_command', methods=['GET', 'POST'])
def send_command():
    aksi = request.args.get('aksi') or request.form.get('aksi')
    if not aksi:
        return jsonify({"success": False, "error": "Parameter 'aksi' dibutuhkan"})

    # Perlakuan khusus jika perintah adalah reset_paket
    if aksi == "reset_paket":
        status_data["status_paket"] = "Tidak Ada"
        print("[Reset Lokal] >> status_paket di-set ke 'Tidak Ada' secara lokal")

    try:
        # Tetap kirim perintah ke Arduino lewat serial
        ser.write((aksi + '\n').encode())
        print(f"[Ke Arduino] >> {aksi}")
    except Exception as e:
        return jsonify({"success": False, "error": f"Gagal kirim ke Arduino: {e}"})

    return jsonify({"success": True, "message": f"Perintah '{aksi}' berhasil dikirim ke Arduino"})

if __name__ == '__main__':
    # Jalankan Flask server
    app.run(host='0.0.0.0', port=5000)
