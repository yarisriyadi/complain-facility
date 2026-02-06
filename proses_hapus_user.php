<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['status']) || $_SESSION['role'] != "admin"){
    exit;
}

if(isset($_GET['id'])){
    // Di dalam proses_hapus_user.php
$id = $_GET['id'];
$cek_user = mysqli_query($conn, "SELECT role FROM users WHERE id='$id'");
$data = mysqli_fetch_assoc($cek_user);

if($data['role'] == 'admin'){
    header("location:admin_manage_users.php?pesan=gagal_hapus_admin");
} else {
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    header("location:admin_manage_users.php?pesan=hapus_berhasil");
}
}
?>