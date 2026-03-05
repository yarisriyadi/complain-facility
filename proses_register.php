<?php
require_once 'config_maintenance.php';
cek_akses_maintenance($maintenance_mode);
include 'koneksi.php';

echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_raw = trim($_POST['username']);
    $nama_raw = trim($_POST['nama_lengkap']);
    $email = strtolower(trim($_POST['email'])); 
    $password_mentah = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; 
    $device_id = $_POST['device_id'];
    $role = 'user'; 
    $nama = ucwords(strtolower($nama_raw));
    $username = $username_raw;
    $hasSymbol = preg_match('/[!@#$%^&*(),.?":{}|<>_]/', $password_mentah);
    $hasNumber = preg_match('/[0-9]/', $password_mentah);

    function kirimRespon($title, $text, $icon, $redirect = 'back') {
        $jsAction = ($redirect === 'back') ? "window.history.back();" : "window.location.href='$redirect';";
        echo "
        <script>
            Swal.fire({
                title: '$title',
                text: '$text',
                icon: '$icon',
                confirmButtonColor: '#28a745'
            }).then(() => {
                $jsAction
            });
        </script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        kirimRespon('Format Salah', 'Format email tidak valid!', 'error');
    }

    if (!preg_match('/^[A-Z]/', $username) || strlen($username) < 3) {
        kirimRespon('Username Tidak Valid', 'Username tidak valid! Harus diawali Huruf Besar.', 'warning');
    }

    if (!preg_match('/^[A-Z]/', $password_mentah) || strlen($password_mentah) < 6 || !$hasSymbol || !$hasNumber) {
        kirimRespon('Password Lemah', 'Password minimal 6 karakter, diawali Huruf Besar, mengandung angka & simbol!', 'warning');
    }

    if ($password_mentah !== $confirm_password) {
        kirimRespon('Tidak Cocok', 'Konfirmasi password tidak cocok!', 'error');
    }

    // CEK USERNAME
    $stmt_cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_cek->bind_param("s", $username);
    $stmt_cek->execute();
    if ($stmt_cek->get_result()->num_rows > 0) {
        kirimRespon('Gagal!', 'Username sudah terdaftar!', 'error');
    }

    // CEK EMAIL
    $stmt_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    if ($stmt_email->get_result()->num_rows > 0) {
        kirimRespon('Gagal!', 'Email sudah terdaftar! Gunakan email lain.', 'error');
    }

    // CEK DEVICE ID
    $stmt_dev = $conn->prepare("SELECT id FROM users WHERE device_id = ?");
    $stmt_dev->bind_param("s", $device_id);
    $stmt_dev->execute();
    if ($stmt_dev->get_result()->num_rows > 0) {
        kirimRespon('Perangkat Terdeteksi', 'Perangkat Anda sudah terdaftar.', 'info', 'login.php');
    }

    // PROSES INSERT
    $password_hashed = password_hash($password_mentah, PASSWORD_DEFAULT);
    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, email, role, device_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("ssssss", $username, $password_hashed, $nama, $email, $role, $device_id);

    if ($stmt_insert->execute()) {
        // Berhasil Registrasi
        kirimRespon('BERHASIL!', 'Akun Anda telah terdaftar. Silakan Login.', 'success', 'login.php');
    } else {
        kirimRespon('ERROR', 'Terjadi kesalahan sistem. Silakan coba lagi nanti.', 'error');
    }

    // Tutup koneksi
    $stmt_cek->close();
    $stmt_email->close();
    $stmt_dev->close();
    $stmt_insert->close();
    $conn->close();
}
?>