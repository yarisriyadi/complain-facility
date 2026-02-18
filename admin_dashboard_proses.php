<?php
session_start();
include 'config_maintenance.php';
cek_akses_maintenance($maintenance_mode);
include 'koneksi.php';

if(!isset($_SESSION['status']) || ($_SESSION['role'] != "admin" && $_SESSION['role'] != "teknisi")){
    header("location:login.php?pesan=belum_login"); exit;
}
$role_login = $_SESSION['role'];
$nama_login = $_SESSION['nama'];
$dashboard_title = ($role_login === 'admin') ? "ADMIN MONITORING" : "TEKNISI MONITORING";

if (isset($_POST['ajax_search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['keyword']);
    $sql = "SELECT c.*, r.foto_after, r.ttd_user, r.ttd_pga FROM complaints c
            LEFT JOIN repair_actions r ON c.complain_id = r.complaint_id
            WHERE (r.ttd_user IS NULL OR r.ttd_user = '' OR r.ttd_pga IS NULL OR r.ttd_pga = '')";
    
    if ($search != "") {
        $sql .= " AND (c.section_dept LIKE '%$search%' OR c.nama_user LIKE '%$search%' OR c.lokasi_kerusakan LIKE '%$search%')";
    }
    
    $q = mysqli_query($conn, $sql . " ORDER BY c.complain_id DESC");
    if (mysqli_num_rows($q) > 0) {
        $no = 1;
        while ($row = mysqli_fetch_array($q)) {
            $has_user = !empty($row['ttd_user']);
            $has_pga  = !empty($row['ttd_pga']);
            echo "<tr>
                    <td>".$no++."</td>
                    <td class='caps'>".htmlspecialchars($row['section_dept'])."</td>
                    <td class='caps'>".htmlspecialchars($row['lokasi_kerusakan'])."</td>
                    <td class='caps'>".htmlspecialchars($row['nama_user'])."</td>
                    <td>".date('d/m/Y', strtotime($row['tanggal']))."</td>
                    <td style='text-align: left;' class='caps'><strong>".nl2br(htmlspecialchars($row['kondisi_current']))."</strong></td>
                    <td><div class='img-container'>";
            
            if(!empty($row['foto_before'])) {
                echo "<img src='uploads/before/".$row['foto_before']."' width='35' height='35' class='zoom-img' style='border-radius:3px; margin: 2px;' title='BEFORE'>";
            }
            
            if(!empty($row['foto_after'])) {
                echo "<img src='uploads/after/".$row['foto_after']."' width='35' height='35' class='zoom-img' style='border-radius:3px; margin: 2px;' title='AFTER'>";
            }

            echo "</div></td>
                    <td>
                        <span class='badge bg-proses'>PROSES</span>
                        <div class='check-list'>
                            <span class='".($has_user ? 'active' : '')."'>✔ USER</span>
                            <span class='".($has_pga ? 'active' : '')."'>✔ PGA</span>
                        </div>
                    </td>
                    <td>
                        <div><a href='edit.php?id=".$row['complain_id']."' class='btn-link'>LIHAT</a></div>";
            if($role_login === 'admin'){
                // PERBAIKAN: Tambahkan parameter asal=proses di link hapus AJAX
                echo "<div style='margin-top: 8px;'><a href='proses.php?hapus=".$row['complain_id']."&asal=proses' class='btn-link btn-delete' onclick='return confirm(\"HAPUS DATA INI?\")'>HAPUS</a></div>";
            }
            echo "</td></tr>";
        }
    } else { echo "<tr><td colspan='9' class='caps'>DATA TIDAK DITEMUKAN.</td></tr>"; }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $dashboard_title; ?> - PROSES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style_theme.css">
    
    <style>
        body { 
        font-family: Arial, sans-serif; margin: 20px; 
            background: var(--bg-color); 
            color: var(--text-color); 
            transition: 0.3s; 
        }
        .container { 
            background: var(--container-bg); 
            padding: 30px; 
            max-width: 1100px; 
            margin: auto; 
            border-radius: 8px; 
            box-shadow: 0 4px 15px var(--shadow); 
        }
        .header-section { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            text-align: center; 
            margin-bottom: 20px; 
            padding-bottom: 15px; 
            border-bottom: none; 
            gap: 10px; 
            text-transform: uppercase; 
        }
        .logo-img { 
            height:50px; width: auto; 
        }
        .header-section h2 { 
            margin: 0; 
            font-size: 1.5rem; 
            color: var(--text-color); 
            font-weight: bold; 
        }
        .user-info { 
            font-size: 12px; 
            color: var(--text-color); 
            opacity: 0.8; 
        }    
        .btn-logout { 
            display: inline-block; 
            margin-top: 5px; 
            color: #dc3545; 
            text-decoration: none; 
            font-weight: bold; 
            border: 1px solid #dc3545; 
            padding: 4px 10px; 
            border-radius: 4px; 
            font-size: 11px; 
            transition: all 0.3s ease; 
        }
        .btn-logout:hover { 
            background: #dc3545; 
            color: #fff; 
            transform: translateY(-2px); 
            box-shadow: 0 3px 8px rgba(220,53,69,0.3); 
        }   
        .nav-tabs { 
            display: flex; 
            margin-bottom: 15px; 
            border-bottom: 1px solid var(--border-color); 
            text-transform: uppercase; 
        }
        .nav-link { 
            flex: 1; 
            text-align: center; 
            padding: 10px 5px; 
            text-decoration: none; 
            background: rgba(128,128,128,0.1); 
            color: var(--text-color); 
            font-size: 12px; 
            font-weight: bold; 
            border-radius: 5px 5px 0 0; 
            border: 1px solid transparent; 
            transition: all 0.3s ease; 
        }
        .nav-link.active { 
            background: #ffc107; 
            color: #fff; 
            border-color: var(--border-color) var(--border-color) transparent; 
        }
        .nav-link:hover:not(.active) { 
            background: rgba(128,128,128,0.2); 
            transform: translateY(-1px); 
        }    
        .search-wrapper { 
            display: flex; 
            flex-direction: column; 
            gap: 10px; margin-bottom: 15px; 
        }
        .input-search { 
            padding: 12px; 
            border: 1px 
            solid var(--border-color); 
            border-radius: 4px; 
            width: 100%; 
            box-sizing: border-box; 
            outline: none; 
            font-size: 14px; 
            background: var(--input-bg); 
            color: var(--input-text); 
            transition: border 0.3s; 
        }
        .input-search:focus { 
            border: 1px solid #ffc107; 
            box-shadow: 0 0 5px rgba(255,193,7,0.2); 
        }
        .table-responsive { 
            width: 100%; 
            overflow-x: auto; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            min-width: 850px; 
            background: transparent; 
        }
        th, td { 
            border: 1px solid var(--border-color); 
            padding: 12px 8px; 
            font-size: 12px; 
            text-align: center; color: var(--text-color); 
        }
        th { 
            background: rgba(128,128,128,0.1); 
            color: var(--text-color); 
            text-transform: uppercase; 
            font-weight: bold; 
        }
        .caps { 
            text-transform: uppercase; 
        }    
        .badge { 
            padding: 5px 10px; 
            border-radius: 12px; 
            font-size: 10px; 
            font-weight: bold; 
            text-transform: uppercase; 
        }
        .bg-proses { 
            background: #fff3cd; 
            color: #856404;
            border: 1px solid #ffeeba; 
        }
        .check-list { 
            font-size: 10px; 
            color: #bbb; 
            margin-top: 8px; 
            display: flex; 
            justify-content: center; 
            gap: 5px; 
        }
        .active { 
            color: #28a745; 
            font-weight: bold; 
        }
        .btn-link { 
            text-decoration: none; 
            color: #007bff; 
            font-weight: bold; 
            font-size: 11px; 
            text-transform: uppercase; 
            display: inline-block; 
            transition: all 0.2s ease; 
        }
        .btn-link:hover { 
            color: #0056b3; 
            transform: scale(1.08); 
        }
        .btn-delete { 
            color: #dc3545; 
        }
        .btn-delete:hover { 
            color: #a71d2a !important; 
        }    
        .modal-overlay { 
            display: none; 
            position: fixed; 
            z-index: 9999; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0,0,0,0.85); 
            justify-content: center; 
            align-items: center; 
            cursor: zoom-out; 
        }
        .modal-overlay img { max-width: 90%; 
            max-height: 90%; 
            transform: scale(0.8); 
            transition: transform 0.3s ease; 
        }
        .modal-overlay.show { 
            display: flex; 
        }
        .modal-overlay.show img { 
            transform: scale(1); 
        }
        @media (min-width: 768px) {
            .header-section { 
                flex-direction: row; 
                justify-content: space-between; 
                text-align: left; 
            }
            .search-wrapper { 
                flex-direction: row; 
                justify-content: space-between; 
                align-items: center; 
            }
            .input-search { 
                width: 350px; 
        }
            .nav-link { 
                flex: none; 
                padding: 10px 25px; 
                font-size: 13px; 
            }
        }
    </style>
</head>
<body data-theme="dark">
<div class="theme-switcher">
    <button class="theme-btn" onclick="toggleTheme()" title="Ganti Tema">
        <i id="theme-icon-sun" class="fa-solid fa-sun" style="color: #f1c40f;"></i>
        <i id="theme-icon-moon" class="fa-solid fa-moon" style="color: #f1c40f;"></i>
    </button>
</div>

<div id="imageModal" class="modal-overlay" onclick="closeZoom()">
    <img id="imgZoomed" src="">
</div>

<div class="container">
    <div class="header-section">
        <img src="bahan/logo.png" alt="Logo" class="logo-img" onerror="this.src='https://via.placeholder.com/150x50?text=SHINSEI'">
        <div>
            <h2><?php echo $dashboard_title; ?></h2>
            <div class="user-info">
                HALO, <strong><?php echo htmlspecialchars(strtoupper($nama_login)); ?></strong>
                <a href="logout.php" class="btn-logout" onclick="return confirm('YAKIN INGIN KELUAR?')">KELUAR</a>
            </div>
        </div>
    </div>

    <div class="nav-tabs">
        <a href="admin_dashboard_proses.php" class="nav-link active">MASIH PROSES</a>
        <?php if($role_login === "admin"): ?>
            <a href="admin_dashboard_selesai.php" class="nav-link">SUDAH SELESAI</a>
            <a href="admin_manage_users.php" class="nav-link" style="background: rgba(128,128,128,0.2);">KELOLA USER</a>
        <?php endif; ?>
    </div>

    <div class="search-wrapper">
        <p style="font-size: 12px; 
        margin: 0; font-weight: bold; text-transform: uppercase; 
        color: var(--text-color);">STATUS: <strong style="color:#ffc107;">MASIH PROSES</strong></p>
        <input type="text" id="keyword" class="input-search" placeholder="Cari data proses..." autocomplete="off">
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="30">NO</th>
                    <th>SECTION / DEPT</th>
                    <th>LOKASI</th>
                    <th>USER</th>
                    <th>TANGGAL</th>
                    <th>KONDISI</th>
                    <th width="100">FOTO B/A</th>
                    <th width="100">STATUS</th>
                    <th width="120">AKSI</th>
                </tr>
            </thead>
            <tbody id="tabel-data">
                <?php
                $no = 1;
                $q = mysqli_query($conn, "SELECT c.*, r.foto_after, r.ttd_user, r.ttd_pga FROM complaints c LEFT JOIN repair_actions r ON c.complain_id = r.complaint_id WHERE (r.ttd_user IS NULL OR r.ttd_user = '' OR r.ttd_pga IS NULL OR r.ttd_pga = '') ORDER BY c.complain_id DESC");
                while($row = mysqli_fetch_array($q)){
                    $has_user = !empty($row['ttd_user']); $has_pga = !empty($row['ttd_pga']);
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td class="caps"><?php echo htmlspecialchars($row['section_dept']); ?></td>
                    <td class="caps"><?php echo htmlspecialchars($row['lokasi_kerusakan']); ?></td>
                    <td class="caps"><?php echo htmlspecialchars($row['nama_user']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                    <td style="text-align: left;" class="caps"><strong><?php echo nl2br(htmlspecialchars($row['kondisi_current'])); ?></strong></td>
                    <td><div class="img-container">
                        <?php 
                        if(!empty($row['foto_before'])) {
                            echo "<img src='uploads/before/".$row['foto_before']."' width='35' height='35' class='zoom-img' style='border-radius:3px; margin: 2px;' title='BEFORE'>";
                        }
                        if(!empty($row['foto_after'])) {
                            echo "<img src='uploads/after/".$row['foto_after']."' width='35' height='35' class='zoom-img' style='border-radius:3px; margin: 2px;' title='AFTER'>";
                        }
                        ?>
                    </div></td>
                    <td>
                        <span class="badge bg-proses">PROSES</span>
                        <div class="check-list">
                            <span class="<?php echo $has_user ? 'active' : ''; ?>">✔ USER</span>
                            <span class="<?php echo $has_pga ? 'active' : ''; ?>">✔ PGA</span>
                        </div>
                    </td>
                    <td>
                        <div><a href="edit.php?id=<?php echo $row['complain_id']; ?>" class="btn-link">LIHAT</a></div>
                        <?php if($role_login === 'admin'): ?>
                        <div style="margin-top: 8px;"><a href="proses.php?hapus=<?php echo $row['complain_id']; ?>&asal=proses" class="btn-link btn-delete" onclick="return confirm('HAPUS DATA INI?')">HAPUS</a></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="theme_script.js"></script> 
<script>
    let idleTime = 0;
    const keepAliveInterval = 30000; 
    let lastKeepAlive = Date.now();

    $(document).ready(function () {
        let idleInterval = setInterval(timerIncrement, 1000); 

        function sendKeepAlive() {
            let now = Date.now();
            if (now - lastKeepAlive > keepAliveInterval) {
                fetch('keep_alive.php')
                    .then(response => {
                        console.log("Session PHP diperbarui");
                        lastKeepAlive = now;
                    })
                    .catch(err => console.warn("Gagal memperbarui session"));
            }
        }

        $(this).on('mousemove keypress mousedown touchstart scroll', function () {
            idleTime = 0; 
            sendKeepAlive(); 
        });
    });

    function timerIncrement() {
        idleTime++;
        if (idleTime >= 60) { 
            window.location.href = "logout.php?pesan=sesi_habis";
        }
    }

    function openZoom(src) { $('#imgZoomed').attr('src', src); $('#imageModal').addClass('show'); }
    function closeZoom() { $('#imageModal').removeClass('show'); }

    $(document).ready(function(){
        $(document).on('click', '.zoom-img', function(){ openZoom($(this).attr('src')); });
        
        $('#keyword').on('keyup', function(){
            $.ajax({
                url: 'admin_dashboard_proses.php', 
                type: 'POST',
                data: { ajax_search: true, keyword: $(this).val() },
                success: function(res){ $('#tabel-data').html(res); }
            });
        });
    });
</script>
</body>
</html>