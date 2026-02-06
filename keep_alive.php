<?php
session_start();
// Hanya memperbarui waktu aktivitas
$_SESSION['last_activity'] = time();
echo "Session updated";
?>