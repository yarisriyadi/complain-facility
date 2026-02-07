<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['ijinkan_reset'])) {
    header("Location: lupa_password.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['email_reset'];
    $pass_baru = $_POST['n_pass'];
    $konfirmasi = $_POST['c_pass'];

    if ($pass_baru !== $konfirmasi) {
        echo "<script>alert('Password tidak cocok!'); window.history.back();</script>";
        exit;
    }

    $password_hashed = password_hash($pass_baru, PASSWORD_DEFAULT);

    $query = "UPDATE users SET 
              password = '$password_hashed', 
              otp_code = NULL, 
              otp_expiry = NULL 
              WHERE email = '$email'";

    if (mysqli_query($conn, $query)) {
        session_destroy();
        echo "<script>alert('Berhasil! Password Anda telah diperbarui.'); window.location='login.php';</script>";
    } else {
        echo "Gagal memperbarui database: " . mysqli_error($conn);
    }
}
?>