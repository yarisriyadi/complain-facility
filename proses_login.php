<?php
session_start();
require_once 'config_maintenance.php';
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    $_SESSION['last_username'] = $identifier;

    $q_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$identifier' OR email='$identifier' LIMIT 1");
    $d_user = mysqli_fetch_assoc($q_user);

    if (!$d_user) {
        $_SESSION['error_msg'] = "Akun tidak ditemukan! Silakan registrasi terlebih dahulu.";
        header("location:login.php");
        exit;
    }

    if (password_verify($password, $d_user['password']) || $password === $d_user['password']) {
        
        // Cek Maintenance Mode
        if ($maintenance_mode && $d_user['role'] !== 'admin') {
            $_SESSION['error_msg'] = "Mohon Maaf, Sistem sedang dalam Maintenance.";
            header("location:login.php");
            exit;
        }

        // Reset session error jika berhasil login
        unset($_SESSION['attempt']);
        unset($_SESSION['last_username']);
        unset($_SESSION['error_msg']);
        
        $_SESSION['status']       = "login";
        $_SESSION['id_user']      = $d_user['id'];
        $_SESSION['role']         = $d_user['role'];
        $_SESSION['username']     = $d_user['username'];
        $_SESSION['nama']         = $d_user['nama_lengkap'];
        $_SESSION['nama_lengkap'] = $d_user['nama_lengkap']; 
        
        if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'teknisi' || $_SESSION['role'] === 'pga') {
            header("location:admin_dashboard_proses.php");
        } else {
            header("location:index.php");
        }
        exit;
    }

    //ika password salah
    $_SESSION['attempt'] = (isset($_SESSION['attempt'])) ? $_SESSION['attempt'] + 1 : 1;
    $_SESSION['error_msg'] = "Password Salah! (" . $_SESSION['attempt'] . "/5)";
    
    header("location:login.php"); 
    exit;
} else {
    header("location:login.php");
    exit;
}