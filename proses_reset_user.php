<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['status']) || $_SESSION['role'] != "admin"){
    exit("Akses Ditolak: Anda tidak memiliki izin untuk melakukan tindakan ini.");
}

if(isset($_GET['id']) && isset($_GET['pass'])){
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $password_baru = $_GET['pass']; 
    
    $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);

    $query = "UPDATE users SET password = '$password_hashed' WHERE id = '$id'";

    if(mysqli_query($conn, $query)){
        echo "<script>
                alert('Berhasil! Password telah dirubah menjadi: " . htmlspecialchars($password_baru) . "');
                window.location='admin_manage_users.php';
              </script>";
    } else {
        echo "Gagal memperbarui data: " . mysqli_error($conn);
    }
} else {
    header("location:admin_manage_users.php");
}
?>