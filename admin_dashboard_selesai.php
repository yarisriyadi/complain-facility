<?php
session_start();
include 'config_maintenance.php';
cek_akses_maintenance($maintenance_mode);
include 'koneksi.php';

if(!isset($_SESSION['status']) || $_SESSION['role'] != "admin"){
    header("location:login.php?pesan=belum_login"); exit;
}
$role_login = $_SESSION['role'];
$nama_login = $_SESSION['nama'];

if (isset($_POST['ajax_search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['keyword']);
    $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
    $limit = 5;

    $sql = "SELECT c.*, r.foto_after, r.ttd_user, r.ttd_pga, r.repair_action FROM complaints c
            INNER JOIN repair_actions r ON c.complain_id = r.complaint_id
            WHERE r.ttd_user != '' AND r.ttd_pga != ''";
    
    if ($search != "") {
        $sql .= " AND (c.section_dept LIKE '%$search%' OR c.nama_user LIKE '%$search%' OR c.lokasi_kerusakan LIKE '%$search%')";
    }
    
    $sql .= " ORDER BY c.complain_id DESC LIMIT $offset, $limit";
    $q = mysqli_query($conn, $sql);

    if (mysqli_num_rows($q) > 0) {
        $no = $offset + 1;
        while ($row = mysqli_fetch_array($q)) {
            $has_user = !empty($row['ttd_user']); $has_pga = !empty($row['ttd_pga']);
            echo "<tr>
                    <td>".$no++."</td>
                    <td class='caps'>".htmlspecialchars($row['section_dept'])."</td>
                    <td class='caps'>".htmlspecialchars($row['lokasi_kerusakan'])."</td>
                    <td class='caps'>".htmlspecialchars($row['nama_user'])."</td>
                    <td>".date('d/m/Y', strtotime($row['tanggal']))."</td>
                    <td style='text-align: left;' class='caps'><strong>".nl2br(htmlspecialchars($row['kondisi_current']))."</strong></td>
                    <td style='text-align: left;' class='caps'>".nl2br(htmlspecialchars($row['repair_action']))."</td>
                    <td><div class='img-container'>";
            if(!empty($row['foto_before'])) echo "<img src='uploads/before/".$row['foto_before']."' width='35' height='35' class='zoom-img' style='border-radius:3px; margin: 2px;' title='BEFORE'>";
            if(!empty($row['foto_after'])) echo "<img src='uploads/after/".$row['foto_after']."' width='35' height='35' class='zoom-img' style='border-radius:3px; margin: 2px;' title='AFTER'>";
            echo "</div></td>
                    <td>
                        <span class='badge bg-selesai'>SELESAI</span>
                        <div class='check-list'>
                            <span class='".($has_user ? 'active' : '')."'>✔ USER</span>
                            <span class='".($has_pga ? 'active' : '')."'>✔ PGA</span>
                        </div>
                    </td>
                    <td>
                        <div><a href='edit.php?id=".$row['complain_id']."' class='btn-link'>LIHAT</a></div>
                        <div style='margin-top: 8px;'><a href='cetak.php?id=".$row['complain_id']."' target='_blank' class='btn-pdf-enabled'>PDF</a></div>
                        <div style='margin-top: 8px;'><a href='proses.php?hapus=".$row['complain_id']."&asal=selesai' class='btn-link btn-delete' onclick='return confirm(\"HAPUS DATA INI?\")'>HAPUS</a></div>
                    </td></tr>";
        }
    } else { 
        if($offset == 0) {
            echo "<tr><td colspan='10' class='caps'>DATA TIDAK DITEMUKAN.</td></tr>"; 
        } else {
            echo "END";
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN MONITORING - SELESAI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('selected-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <link rel="stylesheet" href="style_theme.css">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
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
            height: 50px; 
            width: auto; 
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
            font-size: 12px; font-weight: bold; border-radius: 5px 5px 0 0; border: 1px solid transparent; transition: all 0.3s ease; }
        .nav-link.active { 
            background: #28a745; 
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
            gap: 15px; 
            margin-bottom: 15px; 
        }
        .status-label-container p { 
            font-size: 12px; 
            margin: 0; 
            font-weight: bold; 
            text-transform: uppercase; 
            color: var(--text-color); 
        }
        .status-label-container strong { 
            color: #28a745; 
    }
        .search-controls { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 10px; 
            align-items: center; 
            width: 100%; 
        }
        .search-input-container { 
            flex-grow: 1; 
        }
        .input-search { 
            padding: 12px; 
            border: 1px solid var(--border-color); 
            border-radius: 4px; 
            width: 100%; 
            box-sizing: border-box; 
            outline: none; 
            font-size: 14px; 
            background: var(--input-bg); 
            color: var(--input-text); 
            transition: all 0.3s; 
        }
        .input-search:focus { 
            border: 1px solid #28a745; 
            box-shadow: 0 0 5px rgba(40,167,69,0.2); 
    }    
        .btn-excel { 
            background-color: #28a745; 
            color: white; 
            padding: 10px 18px; 
            border-radius: 4px; 
            text-decoration: none; 
            font-size: 13px; 
            font-weight: bold; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            white-space: nowrap; 
            transition: 0.3s; 
            border: none; 
            cursor: pointer; 
        }
        .btn-excel:hover { 
            background-color: #145532 !important; 
            transform: translateY(-2px); 
            box-shadow: 0 4px 8px rgba(0,0,0,0.2); 
        }

        /* CSS LOAD MORE */
        .show-more-wrapper {
            text-align: center;
            padding: 10px;
            background: transparent;
            border: none;
            margin-top: 15px;
        }
        .btn-show-more {
            background: #444;
            border: 1px solid #666;
            color: #ccc;
            padding: 4px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: normal;
            font-size: 11px;
            transition: 0.3s;
            text-transform: uppercase;
        }
        .btn-show-more:hover {
            background: #555;
            color: #fff;
            border-color: #888;
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
            text-align: center; 
            color: var(--text-color); 
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
        .bg-selesai { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
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
        .btn-link, .btn-pdf-enabled { 
            text-decoration: none; 
            color: #007bff; 
            font-weight: bold; 
            font-size: 11px; 
            text-transform: uppercase; 
            display: inline-block; 
            transition: all 0.2s ease; 
        }
        .btn-pdf-enabled { 
            color: #28a745 !important; 
        }
        .btn-link:hover, .btn-pdf-enabled:hover {
            transform: scale(1.1); 
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
        .modal-overlay img { 
            max-width: 90%; 
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
            .search-controls { 
                width: auto; 
                flex-wrap: nowrap; 
            }
            .search-input-container { 
                width: 350px; 
            }
            .nav-link { 
                flex: none; 
                padding: 10px 25px; 
                font-size: 13px; 
            }
            .img-container {
                display: flex;       
                justify-content: center; 
                align-items: center;     
                gap: 5px;               
                flex-wrap: nowrap;     
            }
            .zoom-img {
                cursor: zoom-in;
                transition: transform 0.2s;
                display: block;        
                object-fit: cover;      
        }
            .zoom-img:hover {
                transform: scale(1.1);
            }
            .theme-switcher {
            position: fixed;
            bottom: 25px;
            left: 25px;
            z-index: 1000;
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
            <h2>ADMIN MONITORING</h2>
            <div class="user-info">
                HALO, <strong><?php echo htmlspecialchars(strtoupper($nama_login)); ?></strong>
                <a href="logout.php" class="btn-logout" onclick="return confirm('YAKIN INGIN KELUAR?')">KELUAR</a>
            </div>
        </div>
    </div>

    <div class="nav-tabs">
        <a href="admin_dashboard_proses.php" class="nav-link">MASIH PROSES</a>
        <a href="admin_dashboard_selesai.php" class="nav-link active">SUDAH SELESAI</a>
        <a href="admin_manage_users.php" class="nav-link" style="background: rgba(128,128,128,0.2);">KELOLA USER</a>
    </div>

    <div class="search-wrapper">
        <div class="status-label-container">
            <p>STATUS: <strong>SUDAH SELESAI</strong></p>
        </div>
        <div class="search-controls">
            <a href="generate_excel.php" class="btn-excel">
                <i class="fa-solid fa-file-excel"></i> DOWNLOAD EXCEL
            </a>
            <div class="search-input-container">
                <input type="text" id="keyword" class="input-search" placeholder="Cari data selesai..." autocomplete="off">
            </div>
        </div>
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
                    <th>PERBAIKAN</th> 
                    <th width="100">FOTO B/A</th>
                    <th width="100">STATUS</th>
                    <th width="120">AKSI</th>
                </tr>
            </thead>
            <tbody id="tabel-data">
                <?php
                $no = 1;
                // Query awal dibatasi 5 data
                $q = mysqli_query($conn, "SELECT c.*, r.foto_after, r.ttd_user, r.ttd_pga, r.repair_action FROM complaints c INNER JOIN repair_actions r ON c.complain_id = r.complaint_id WHERE r.ttd_user != '' AND r.ttd_pga != '' ORDER BY c.complain_id DESC LIMIT 5");
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
                    <td style="text-align: left;" class="caps"><?php echo nl2br(htmlspecialchars($row['repair_action'])); ?></td> 
                    <td><div class="img-container">
                        <?php if(!empty($row['foto_before'])) echo "<img src='uploads/before/".$row['foto_before']."' width='35' height='35' class='zoom-img' style='border-radius:3px; margin: 2px;' title='BEFORE'>"; ?>
                        <?php if(!empty($row['foto_after'])) echo "<img src='uploads/after/".$row['foto_after']."' width='35' height='35' class='zoom-img' style='border-radius:3px; margin: 2px;' title='AFTER'>"; ?>
                    </div></td>
                    <td>
                        <span class="badge bg-selesai">SELESAI</span>
                        <div class="check-list">
                            <span class="<?php echo $has_user ? 'active' : ''; ?>">✔ USER</span>
                            <span class="<?php echo $has_pga ? 'active' : ''; ?>">✔ PGA</span>
                        </div>
                    </td>
                    <td>
                        <div><a href="edit.php?id=<?php echo $row['complain_id']; ?>" class="btn-link">LIHAT</a></div>
                        <div style="margin-top: 8px;"><a href="cetak.php?id=<?php echo $row['complain_id']; ?>" target="_blank" class="btn-pdf-enabled">PDF</a></div>
                        <div style="margin-top: 8px;"><a href="proses.php?hapus=<?php echo $row['complain_id']; ?>&asal=selesai" class="btn-link btn-delete" onclick="return confirm('HAPUS DATA INI?')">HAPUS</a></div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="show-more-wrapper">
        <button type="button" id="btn-load-more" class="btn-show-more">TAMPILKAN LEBIH BANYAK</button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="theme_script.js"></script>

<script>
    function openZoom(src) { $('#imgZoomed').attr('src', src); $('#imageModal').addClass('show'); }
    function closeZoom() { $('#imageModal').removeClass('show'); }
    
    $(document).ready(function(){
        let idleTime = 0;
        const keepAliveInterval = 30000; 
        let lastKeepAlive = Date.now();
        const limit = 5;
        let offset = 5;

        setInterval(function() {
            idleTime++;
            if (idleTime >= 60) {
                window.location.href = "logout.php?pesan=sesi_habis";
            }
        }, 1000);

        function sendKeepAlive() {
            let now = Date.now();
            if (now - lastKeepAlive > keepAliveInterval) {
                fetch('keep_alive.php')
                    .then(() => {
                        console.log("Sesi server diperbarui");
                        lastKeepAlive = now;
                    })
                    .catch(err => console.warn("Keep-alive gagal"));
            }
        }

        $(this).on('mousemove keypress mousedown touchstart scroll', function() { 
            idleTime = 0; 
            sendKeepAlive();
        });

        // LOGIKA LOAD MORE
        $('#btn-load-more').on('click', function(){
            const keyword = $('#keyword').val();
            const btn = $(this);
            btn.text('MEMUAT...');

            $.ajax({
                url: 'admin_dashboard_selesai.php',
                type: 'POST',
                data: { 
                    ajax_search: true, 
                    keyword: keyword,
                    offset: offset 
                },
                success: function(response){
                    if(response.trim() === "END") {
                        btn.fadeOut();
                    } else {
                        $('#tabel-data').append(response);
                        offset += limit;
                        btn.text('TAMPILKAN LEBIH BANYAK');
                    }
                }
            });
        });

        $('#keyword').on('keyup', function(){
            const keyword = $(this).val();
            offset = 0;

            $.ajax({
                url: 'admin_dashboard_selesai.php', 
                type: 'POST',
                data: { 
                    ajax_search: true, 
                    keyword: keyword,
                    offset: 0
                },
                success: function(res){ 
                    $('#tabel-data').html(res); 
                    offset = limit;
                    $('#btn-load-more').fadeIn().text('TAMPILKAN LEBIH BANYAK');
                }
            });
        });

        $(document).on('click', '.zoom-img', function(){ openZoom($(this).attr('src')); });
    });
</script>
</body>
</html>