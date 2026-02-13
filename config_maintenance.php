<?php
$maintenance_mode = false; //true or false

function cek_akses_maintenance($status) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($status === true) {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return; 
        }
        $currentPage = basename($_SERVER['PHP_SELF']);
        $allowedPages = ['maintenance.php', 'login.php', 'logout.php'];

        if (!in_array($currentPage, $allowedPages)) {
            header("Location: maintenance.php");
            exit();
        }
    }
}
?>