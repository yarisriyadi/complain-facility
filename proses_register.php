<?php
require_once 'config_maintenance.php';
cek_akses_maintenance($maintenance_mode);
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dan bersihkan spasi
    $username_raw = trim($_POST['username']);
    $nama_raw = trim($_POST['nama_lengkap']);
    
    // PAKSA EMAIL MENJADI HURUF KECIL (Mencegah typo Besar/Kecil)
    $email = strtolower(trim($_POST['email'])); 
    
    $password_mentah = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; 
    $device_id = $_POST['device_id'];
    $role = 'user'; 

    // 2. OTOMATISASI FORMAT
    $nama = ucwords(strtolower($nama_raw));
    $username = $username_raw;

    // 3. Validasi Keamanan
    $hasSymbol = preg_match('/[!@#$%^&*(),.?":{}|<>_]/', $password_mentah);
    $hasNumber = preg_match('/[0-9]/', $password_mentah);

    // Cek format email valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<script>alert('Format email tidak valid!'); window.history.back();</script>");
    }

    if (!preg_match('/^[A-Z]/', $username) || strlen($username) < 3) {
        die("<script>alert('Username tidak valid! Harus diawali Huruf Besar.'); window.history.back();</script>");
    }

    if (!preg_match('/^[A-Z]/', $password_mentah) || strlen($password_mentah) < 6 || !$hasSymbol || !$hasNumber) {
        die("<script>alert('Password minimal 6 karakter, diawali Huruf Besar, mengandung angka & simbol!'); window.history.back();</script>");
    }

    if ($password_mentah !== $confirm_password) {
        die("<script>alert('Konfirmasi password tidak cocok!'); window.history.back();</script>");
    }

    // --- BAGIAN KEAMANAN DATABASE (PREPARED STATEMENTS) ---

    // 4. CEK USERNAME
    $stmt_cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_cek->bind_param("s", $username);
    $stmt_cek->execute();
    if ($stmt_cek->get_result()->num_rows > 0) {
        die("<script>alert('Username sudah terdaftar!'); window.history.back();</script>");
    }

    // 5. TAMBAHAN: CEK EMAIL (Sudah dikonversi ke huruf kecil)
    $stmt_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    if ($stmt_email->get_result()->num_rows > 0) {
        die("<script>alert('Email sudah terdaftar! Gunakan email lain.'); window.history.back();</script>");
    }

    // 6. Cek Duplikasi Device
    $stmt_dev = $conn->prepare("SELECT id FROM users WHERE device_id = ?");
    $stmt_dev->bind_param("s", $device_id);
    $stmt_dev->execute();
    if ($stmt_dev->get_result()->num_rows > 0) {
        die("<script>alert('Perangkat Anda sudah terdaftar.'); window.location='login.php';</script>");
    }

    // 7. Hash Password & Simpan
    $password_hashed = password_hash($password_mentah, PASSWORD_DEFAULT);

    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, email, role, device_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("ssssss", $username, $password_hashed, $nama, $email, $role, $device_id);

    if ($stmt_insert->execute()) {
        header("Location: login.php?pesan=berhasil_regis");
    } else {
        echo "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
    }

    $stmt_cek->close();
    $stmt_email->close();
    $stmt_dev->close();
    $stmt_insert->close();
    $conn->close();
}
?>