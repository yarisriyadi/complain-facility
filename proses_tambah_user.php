<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['role'] != "admin") {
    header("location:admin_dashboard_proses.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Tambah User</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style_theme.css">
    <style>
        body {
            background: var(--bg-color, #121212);
            color: var(--text-color, #ffffff);
            font-family: Arial, sans-serif;
        }
        .swal2-popup {
            background: var(--container-bg, #1e1e1e) !important;
            color: var(--text-color, #ffffff) !important;
            border: 1px solid var(--border-color, #333);
            border-radius: 12px !important;
        }
        .swal2-title, .swal2-html-container {
            color: var(--text-color, #ffffff) !important;
        }
    </style>
</head>
<body>
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username     = mysqli_real_escape_string($conn, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email        = mysqli_real_escape_string($conn, $_POST['email']);
    $password     = $_POST['password']; 
    $role         = mysqli_real_escape_string($conn, $_POST['role']);

    if (empty($username) || empty($nama_lengkap) || empty($password) || empty($role)) {
        echo "<script>
                Swal.fire({
                    title: 'GAGAL!',
                    text: 'Harap lengkapi seluruh form data yang wajib!',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OKE'
                }).then(() => {
                    window.location.href = 'admin_manage_users.php';
                });
              </script>";
        exit;
    }

    $query_cek = "SELECT id FROM users WHERE username = '$username'";
    if (!empty($email)) {
        $query_cek .= " OR email = '$email'";
    }
    
    $cek_user = mysqli_query($conn, $query_cek);
    
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>
                Swal.fire({
                    title: 'GAGAL!',
                    text: 'Username atau Email sudah terdaftar!',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OKE'
                }).then(() => {
                    window.location.href = 'admin_manage_users.php';
                });
              </script>";
        exit;
    }

    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
    $device_id = NULL;

    $sql = "INSERT INTO users (username, nama_lengkap, email, password, role, device_id) 
            VALUES ('$username', '$nama_lengkap', '$email', '$password_hashed', '$role', NULL)";

    if (mysqli_query($conn, $sql)) {
        header("location:admin_manage_users.php?status=add_success");
        exit;
    } else {
        $error_db = mysqli_real_escape_string($conn, mysqli_error($conn));
        echo "<script>
                Swal.fire({
                    title: 'ERROR SISTEM!',
                    text: 'Gagal menyimpan data ke database: " . $error_db . "',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'OKE'
                }).then(() => {
                    window.location.href = 'admin_manage_users.php';
                });
              </script>";
        exit;
    }

} else {
    header("location:admin_manage_users.php");
    exit;
}
?>
</body>
</html>