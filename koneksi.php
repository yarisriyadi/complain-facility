<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "complain-facility";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$halaman_bebas = ['login.php', 'register.php', 'proses_register.php', 'proses_login.php', 'verifikasi.php', 'proses_kirim_otp.php',
                'lupa_password.php', 'lupa_email.php'];
$halaman_sekarang = basename($_SERVER['PHP_SELF']);

if (!in_array($halaman_sekarang, $halaman_bebas)) {
    
    $timeout_limit = 60; 

    if (isset($_SESSION['last_activity'])) {
        $duration = time() - $_SESSION['last_activity'];
        
        if ($duration > $timeout_limit) {
            session_unset();
            session_destroy();
            
            header("Location: login.php?pesan=sesi_habis");
            exit;
        }
    }

    $_SESSION['last_activity'] = time();
}
?>