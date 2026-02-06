<?php
session_start();
session_unset();
session_destroy();

// Cek apakah logout karena sesi habis atau logout manual
$pesan = (isset($_GET['pesan']) && $_GET['pesan'] == 'sesi_habis') ? 'sesi_habis' : 'logout';

header("location:login.php?pesan=" . $pesan);
exit;
?>