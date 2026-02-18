<?php 
session_start();
include 'config_maintenance.php';

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

cek_akses_maintenance($maintenance_mode);
include 'koneksi.php';

if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit;
}

$role_login = $_SESSION['role'];
$nomor_admin   = "6282299058274"; // Nomor Admin

function checkFolder($path) {
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}

if (isset($_POST['simpan'])) {
    $user_id = $_SESSION['id_user']; 
    $dept    = mysqli_real_escape_string($conn, $_POST['section_dept']);
    $lokasi  = mysqli_real_escape_string($conn, $_POST['lokasi_kerusakan']);
    $tgl     = $_POST['tanggal']; 
    $user    = mysqli_real_escape_string($conn, $_SESSION['nama']);
    $cur     = mysqli_real_escape_string($conn, $_POST['kondisi_current']); 
    
    $foto_baru = "";
    if(!empty($_FILES['foto_before']['name'])) {
        $nama_asli = $_FILES['foto_before']['name']; 
        $tmp       = $_FILES['foto_before']['tmp_name'];
        $ekstensi  = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
        
        if($ekstensi != "jpg" && $ekstensi != "jpeg") {
            echo "<script>alert('Gagal! Foto harus berformat JPG atau JPEG.'); window.history.back();</script>";
            exit;
        }
        
        $target_dir = 'uploads/before/';
        checkFolder($target_dir);
        $foto_baru  = $user_id . "_" . date('dmY') . "_BEF" . substr(uniqid(),-3) . "." . $ekstensi;
        move_uploaded_file($tmp, $target_dir . $foto_baru);
    }

    $sql = "INSERT INTO complaints (user_id, section_dept, lokasi_kerusakan, tanggal, nama_user, kondisi_current, foto_before, status) 
            VALUES ('$user_id', '$dept', '$lokasi', '$tgl', '$user', '$cur', '$foto_baru', 'Proses')";
    
    if (mysqli_query($conn, $sql)) { 
        header("Location: index.php?status=sukses");
    } else { 
        echo "Error: " . mysqli_error($conn);
    }
    exit;
}

if(isset($_POST['update']) || isset($_POST['wa_teknisi'])){
    $id_complaint  = mysqli_real_escape_string($conn, $_POST['id']);
    $user_id       = $_SESSION['id_user'];
    
    $nomor_teknisi_pilihan = isset($_POST['wa_teknisi']) ? mysqli_real_escape_string($conn, $_POST['wa_teknisi']) : "";

    $cek_db = mysqli_query($conn, "SELECT id_repair, foto_after, repair_action, ttd_user, ttd_pga FROM repair_actions WHERE complaint_id='$id_complaint'");
    $data_repair = mysqli_fetch_array($cek_db);

    $repair_action_input = mysqli_real_escape_string($conn, $_POST['repair_action']);
    $ttd_user_input      = $_POST['ttd_user']; 
    $ttd_pga_input       = $_POST['ttd_pga'];

    $is_teknisi_role = ($role_login == 'teknisi' || $role_login == 'admin' || $role_login == 'pga');

    if ($is_teknisi_role) {
        $repair_action_final = $repair_action_input;
    } else {
        $repair_action_final = ($data_repair) ? $data_repair['repair_action'] : "";
    }

    $foto_name_lama = ($data_repair) ? $data_repair['foto_after'] : ""; 
    $foto_name_final = $foto_name_lama;

    if($is_teknisi_role && !empty($_FILES['foto_after']['name'])){
        $tmp_file = $_FILES['foto_after']['tmp_name'];
        $ekstensi = strtolower(pathinfo($_FILES['foto_after']['name'], PATHINFO_EXTENSION));
        
        if($ekstensi != "jpg" && $ekstensi != "jpeg") {
            echo "<script>alert('Gagal! Foto harus berformat JPG atau JPEG.'); window.history.back();</script>";
            exit;
        }

        $target_dir = "uploads/after/";
        checkFolder($target_dir); 
        $new_foto_name = $user_id . "_" . date('dmY') . "_AFT" . substr(uniqid(),-3) . "." . $ekstensi;
        if(move_uploaded_file($tmp_file, $target_dir . $new_foto_name)){
            if(!empty($foto_name_lama) && file_exists($target_dir . $foto_name_lama)) unlink($target_dir . $foto_name_lama);
            $foto_name_final = $new_foto_name; 
        }
    }

    if ($data_repair) {
        $query = "UPDATE repair_actions SET 
                    repair_action = '$repair_action_final', 
                    foto_after = '$foto_name_final', 
                    ttd_user = '$ttd_user_input', 
                    ttd_pga = '$ttd_pga_input' 
                  WHERE complaint_id = '$id_complaint'";
    } else {
        $query = "INSERT INTO repair_actions (complaint_id, repair_action, foto_after, ttd_user, ttd_pga) 
                  VALUES ('$id_complaint', '$repair_action_final', '$foto_name_final', '$ttd_user_input', '$ttd_pga_input')";
    }
    
    $success = mysqli_query($conn, $query);

    if($success){
        $info_q = mysqli_query($conn, "SELECT section_dept, lokasi_kerusakan, nama_user, kondisi_current FROM complaints WHERE complain_id = '$id_complaint'");
        $info = mysqli_fetch_assoc($info_q);

        $wa_link = null;

        $is_new_user_signature = (empty($data_repair['ttd_user']) && !empty($ttd_user_input));
        if ($role_login == 'user' && $is_new_user_signature && !empty($nomor_teknisi_pilihan)) {
            $pesan_wa = "*COMPLAIN FACILITY*\n\n";
            $pesan_wa .= "Halo Teknisi, User telah mengisi laporan perbaikan:\n";
            $pesan_wa .= "===============================================\n";
            $pesan_wa .= "👤 *User:* ".$info['nama_user']."\n";
            $pesan_wa .= "🏢 *Dept:* ".$info['section_dept']."\n";
            $pesan_wa .= "📍 *Lokasi:* ".$info['lokasi_kerusakan']."\n";
            $pesan_wa .= "⚠️ *Masalah:* ".$info['kondisi_current']."\n";
            $pesan_wa .= "===============================================\n";
            $pesan_wa .= "Mohon segera diproses perbaikannya. Terima kasih.";
            
            $wa_link = "https://api.whatsapp.com/send?phone=$nomor_teknisi_pilihan&text=" . urlencode($pesan_wa);
        }

        $is_new_repair_action = (empty($data_repair['repair_action']) && !empty($repair_action_final));
        if($is_teknisi_role && $is_new_repair_action && empty($ttd_pga_input) && empty($wa_link)){
            $pesan_wa = "*COMPLAIN FACILITY*\n\n";
            $pesan_wa .= "Halo Admin/PGA, perbaikan telah selesai dikerjakan:\n";
            $pesan_wa .= "===============================================\n";
            $pesan_wa .= "🏢 *Dept:* ".$info['section_dept']."\n";
            $pesan_wa .= "📍 *Lokasi:* ".$info['lokasi_kerusakan']."\n";
            $pesan_wa .= "⚠️ *Masalah:* ".$info['kondisi_current']."\n";
            $pesan_wa .= "🛠️ *Tindakan:* $repair_action_final\n";
            $pesan_wa .= "===============================================\n";
            $pesan_wa .= "Status: *Menunggu Verifikasi & Tanda Tangan*.";
            
            $wa_link = "https://api.whatsapp.com/send?phone=$nomor_admin&text=" . urlencode($pesan_wa);
        }

        $res_repair = mysqli_query($conn, "SELECT id_repair FROM repair_actions WHERE complaint_id = '$id_complaint'");
        $row_repair = mysqli_fetch_assoc($res_repair);
        $rid = $row_repair['id_repair'];

        if(!empty($ttd_user_input) && !empty($ttd_pga_input)){
            mysqli_query($conn, "UPDATE complaints SET repair_id = '$rid', status='Selesai' WHERE complain_id='$id_complaint'");
            $redirect = "admin_dashboard_selesai.php";
        } else {
            mysqli_query($conn, "UPDATE complaints SET repair_id = '$rid', status='Proses' WHERE complain_id='$id_complaint'");
            $redirect = ($role_login == 'user') ? "index.php" : "admin_dashboard_proses.php";
        }

        if($wa_link){
            echo "<script>alert('Berhasil disimpan! Menghubungkan ke WhatsApp...'); window.location.href = '$wa_link';</script>";
        } else {
            header("Location: $redirect?status=sukses");
        }
        exit;
    }
}

// BAGIAN HAPUS (Sudah Disinkronkan dengan Dashboard Proses)
if (isset($_GET['hapus'])) {
    if($role_login != 'admin' && $role_login != 'pga'){
        header("Location: admin_dashboard_proses.php?pesan=akses_ditolak");
        exit;
    }

    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    // CEK PARAMETER ASAL (Jika 'proses' maka redirect ke dashboard proses)
    $asal = isset($_GET['asal']) ? $_GET['asal'] : 'selesai';
    
    $cek_foto = mysqli_query($conn, "SELECT c.foto_before, r.foto_after FROM complaints c LEFT JOIN repair_actions r ON c.complain_id=r.complaint_id WHERE c.complain_id='$id'");
    $f = mysqli_fetch_array($cek_foto);
    
    if($f) {
        if(!empty($f['foto_before']) && file_exists("uploads/before/".$f['foto_before'])) unlink("uploads/before/".$f['foto_before']);
        if(!empty($f['foto_after']) && file_exists("uploads/after/".$f['foto_after'])) unlink("uploads/after/".$f['foto_after']);
    }

    mysqli_query($conn, "DELETE FROM repair_actions WHERE complaint_id='$id'");
    mysqli_query($conn, "DELETE FROM complaints WHERE complain_id='$id'");

    // REDIRECT DINAMIS BERDASARKAN HALAMAN ASAL
    if ($asal == 'proses') {
        header("Location: admin_dashboard_proses.php?pesan=hapus_berhasil");
    } else {
        header("Location: admin_dashboard_selesai.php?pesan=hapus_berhasil");
    }
    exit;
}
?>