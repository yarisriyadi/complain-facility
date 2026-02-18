<?php
session_start();
include 'config_maintenance.php';

cek_akses_maintenance($maintenance_mode); 

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php");
    exit;
}

include 'koneksi.php';

$role_login = $_SESSION['role'];
$nama_login = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'];

if ($role_login == 'admin' || $role_login == 'teknisi') {
    header("location:admin_dashboard_proses.php");
    exit;
}

if (isset($_POST['ajax_search'])) {
    if ($maintenance_mode && $role_login != 'admin') {
        echo "<tr><td colspan='9' style='color:red; font-weight:bold;'>SISTEM SEDANG MAINTENANCE.</td></tr>";
        exit;
    }

    $search = mysqli_real_escape_string($conn, $_POST['keyword']);
    $sql = "SELECT c.*, r.foto_after, r.ttd_user, r.ttd_pga
            FROM complaints c
            LEFT JOIN repair_actions r ON c.complain_id = r.complaint_id "; 

    if ($search != "") {
        $sql .= " WHERE c.section_dept LIKE '%$search%'
                  OR c.lokasi_kerusakan LIKE '%$search%'
                  OR c.nama_user LIKE '%$search%'
                  OR c.kondisi_current LIKE '%$search%' ";
    }

    $sql .= " ORDER BY c.complain_id DESC"; 
    $q = mysqli_query($conn, $sql);

    if (mysqli_num_rows($q) > 0) {
        $no = 1;
        while ($row = mysqli_fetch_array($q)) {
            $has_user = !empty($row['ttd_user']);
            $has_pga  = !empty($row['ttd_pga']);
            $is_done  = ($has_user && $has_pga);

            echo "<tr>
                    <td>" . $no++ . "</td>
                    <td class='caps'>" . htmlspecialchars($row['section_dept']) . "</td>
                    <td class='caps'>" . htmlspecialchars($row['lokasi_kerusakan']) . "</td>
                    <td class='caps'>" . htmlspecialchars($row['nama_user']) . "</td>
                    <td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>
                    <td style='text-align: left;' class='caps'><strong>" . nl2br(htmlspecialchars($row['kondisi_current'])) . "</strong></td>
                    <td>
                        <div class='img-container'>";
            if (!empty($row['foto_before'])) echo "<img src='uploads/before/" . $row['foto_before'] . "' width='35' height='35' class='zoom-img' title='BEFORE' style='border-radius:3px; margin: 2px;'>";
            if (!empty($row['foto_after'])) echo "<img src='uploads/after/" . $row['foto_after'] . "' width='35' height='35' class='zoom-img' style='border-radius:3px; margin: 2px;' title='AFTER'>";
            echo "      </div>
                    </td>
                    <td>
                        <span class='badge " . ($is_done ? 'bg-selesai' : 'bg-proses') . "'>" . ($is_done ? 'SELESAI' : 'PROSES') . "</span>
                        <div class='check-list'>
                            <span class='" . ($has_user ? 'active' : '') . "'>✔ USER</span>
                            <span class='" . ($has_pga ? 'active' : '') . "'>✔ PGA</span>
                        </div>
                    </td>
                    <td>
                        <a href='edit.php?id=" . $row['complain_id'] . "' class='btn-link'>LIHAT</a><br>";

            if ($has_pga) {
                echo "<div style='margin-top: 5px;'><a href='cetak.php?id=" . $row['complain_id'] . "' target='_blank' class='btn-pdf-enabled'>PDF</a></div>";
            } else {
                echo "<div style='margin-top: 5px;'><span class='btn-pdf-disabled' title='MENUNGGU TTD PGA'>PDF</span></div>";
            }
            echo "  </td>
                    </tr>";
        }
    } else {
        echo "<tr><td colspan='9' class='caps'>DATA TIDAK DITEMUKAN.</td></tr>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHINSEI - COMPLAIN FACILITY</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style_theme.css">
    
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: var(--bg-color); 
            color: var(--text-color); 
            transition: 0.3s; 
            overflow-y: scroll; 
        }
        .container { 
            background: var(--container-bg); 
            padding: 30px; 
            max-width: 1100px; 
            margin: auto; 
            border-radius: 8px; 
            box-shadow: 0 4px 15px var(--shadow); 
            min-height: 600px; 
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
            text-decoration: none !important; 
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
        form { 
            background: rgba(128, 128, 128, 0.05); 
            padding: 20px; 
            border: 1px solid var(--border-color); 
            border-radius: 6px; 
            margin-bottom: 25px; 
            position: relative; 
        }
        label {
            display: block; 
            margin-top: 15px; 
            margin-bottom: 5px; 
            font-weight: bold; 
            font-size: 14px; 
            color: var(--text-color); 
            text-transform: uppercase; 
        }    
        input[type="text"], input[type="file"], textarea { 
            width: 100%; 
            padding: 10px; 
            margin: 5px 0; 
            border: 1px solid var(--border-color); 
            border-radius: 4px; 
            font-size: 14px; 
            font-family: Arial, sans-serif; 
            box-sizing: border-box; 
            background: var(--input-bg); 
            color: var(--text-color);
        }
        textarea { 
            height: 80px; 
            resize: vertical; 
        }
        .readonly-input { 
            background: rgba(0,0,0,0.05) !important; 
            opacity: 0.6; 
            cursor: not-allowed; 
            text-transform: uppercase; 
        }    
        button[name="simpan"] { 
            width: 100%; 
            padding: 12px; 
            background: #007bff; 
            color: white; 
            border: none; 
            cursor: pointer; 
            border-radius: 4px; 
            margin-top: 15px; 
            font-weight: bold; 
            font-size: 16px; 
            text-transform: uppercase; 
            transition: all 0.3s ease; 
        }
        button[name="simpan"]:hover:not(:disabled) { 
            background: #0056b3; 
            transform: translateY(-2px); 
            box-shadow: 0 4px 10px rgba(0,123,255,0.3); 
        }
        button[name="simpan"]:disabled { 
            background: #999; 
            cursor: not-allowed; 
        }
        .maintenance-warning { 
            background: #fff3cd; 
            color: #856404; 
            padding: 15px; 
            border: 1px solid #ffeeba; 
            border-radius: 4px; 
            margin-bottom: 15px; 
            text-align: center; 
            font-weight: bold; 
        }
        .search-wrapper { 
            display: flex; 
            justify-content: flex-end; 
            margin-bottom: 15px; 
            width: 100%; 
        }        
        .input-search { 
            padding: 8px 12px; 
            border: 1px solid var(--border-color); 
            border-radius: 4px; width: 100%; 
            max-width: 200px; background: var(--input-bg); 
            color: var(--text-color); 
            outline: none; 
            font-size: 13px; 
            transition: all 0.3s; 
        }
        .input-search:focus { 
            border: 1px solid #007bff; 
            max-width: 250px; 
        }
        .table-responsive { 
            width: 100%; 
            overflow-x: auto; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            min-width: 900px; 
            background: var(--container-bg); 
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
        .zoom-img { 
            cursor: pointer; 
            transition: 0.3s; 
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
        .badge { 
            padding: 5px 10px; 
            border-radius: 12px; 
            font-size: 10px; 
            font-weight: bold; 
        }
        .bg-selesai { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
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
        .btn-link, .btn-pdf-enabled { 
            text-decoration: none !important; 
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
            transform: scale(1.08); 
        }
        .btn-pdf-disabled { 
            font-size: 11px; 
            color: #ccc; 
            font-weight: bold; 
            text-transform: uppercase; 
            cursor: not-allowed; 
            text-decoration: none !important; 
        }
        .welcome-modal {
            display: flex; 
            position: fixed; 
            z-index: 999999; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8); 
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            padding: 15px;
            box-sizing: border-box;
        }
        .welcome-content {
            background: var(--container-bg);
            color: var(--text-color);
            padding: 20px;
            border-radius: 15px;
            width: 100%;
            max-width: 400px;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            animation: bounceIn 0.5s ease;
        }
        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.3); }
            50% { opacity: 1; transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }
        .welcome-content h3 {
            margin-top: 0;
            color: #007bff;
            font-size: 1.2rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }
        .rules-list {
            text-align: left;
            font-size: 13px;
            padding-left: 20px;
            line-height: 1.5;
        }
        .btn-close-welcome {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
        }

        @media (min-width: 768px) { 
            .header-section { 
                flex-direction: row; 
                justify-content: space-between; 
                text-align: left; 
            } 
        }
    </style>
</head>
<body data-theme="dark"> 

<?php if (!isset($_SESSION['welcome_shown'])): ?>
<div id="welcomeModal" class="welcome-modal">
    <div class="welcome-content">
        <h3><i class="fa-solid fa-circle-check"></i> SELAMAT DATANG!</h3>
        <p>Halo <strong><?php echo strtoupper(htmlspecialchars($nama_login)); ?></strong>, Anda berhasil login ke sistem Complain Facility.</p>
        
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 15px 0;">
        
        <strong><i class="fa-solid fa-circle-info"></i> CARA PEMAKAIAN:</strong>
        <ul class="rules-list">
            <li>Isi form <strong>Section</strong>, <strong>Lokasi</strong>, dan <strong>Detail Kerusakan</strong> 
            dengan jelas.</li>
            <li>Wajib melampirkan <strong>Foto Before</strong> (Kondisi saat ini).</li>
            <li>Klik tombol <strong>Kirim</strong> untuk melaporkan keluhan.</li>
            <li>Pastikan Anda <strong>Menandatangani (TTD)</strong> laporan pada menu <strong>LIHAT</strong> 
            agar keluhan segera ditangani oleh Teknisi.</li>
            <li>Anda dapat memantau status pengerjaan (PROSES/SELESAI) pada tabel di bawah.</li>
            <li>Tombol <strong>PDF</strong> akan aktif jika laporan sudah diverifikasi oleh PGA.</li>
        </ul>

        <button class="btn-close-welcome" onclick="closeWelcome()">SAYA MENGERTI</button>
    </div>
</div>
<?php $_SESSION['welcome_shown'] = true; endif; ?>

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
            <h2>COMPLAIN FACILITY</h2>
            <div class="user-info">
                HALO, <strong><?php echo strtoupper(htmlspecialchars($nama_login)); ?></strong>
                <a href="logout.php" class="btn-logout" onclick="return confirm('YAKIN INGIN KELUAR?')">KELUAR</a>
            </div>
        </div>
    </div>

    <?php if ($maintenance_mode && $role_login != 'admin'): ?>
        <div class="maintenance-warning">
            PERHATIAN: SISTEM SEDANG MAINTENANCE. PENGAJUAN KELUHAN DIHENTIKAN SEMENTARA.
        </div>
    <?php endif; ?>

    <form action="proses.php" method="POST" enctype="multipart/form-data">
        <label>SECTION / DEPT :</label>
        <input type="text" name="section_dept" required placeholder="PGA"
            oninvalid="this.setCustomValidity('Tidak boleh kosong!')"
            oninput="this.setCustomValidity('')"
            <?php echo ($maintenance_mode && $role_login != 'admin') ? 'disabled' : ''; ?>>

        <label>LOKASI KERUSAKAN :</label>
        <input type="text" name="lokasi_kerusakan" required placeholder="Lantai 1"
            oninvalid="this.setCustomValidity('Tidak boleh kosong!')"
            oninput="this.setCustomValidity('')"
            <?php echo ($maintenance_mode && $role_login != 'admin') ? 'disabled' : ''; ?>>

        <label>USER (PELAPOR) :</label>
        <input type="text" name="nama_user" value="<?php echo strtoupper(htmlspecialchars($nama_login)); ?>" readonly class="readonly-input">

        <label>CONDITION (DETAIL KERUSAKAN) :</label>
        <textarea name="kondisi_current" required placeholder="Tuliskan detail kerusakan di sini..."
            oninvalid="this.setCustomValidity('Tidak boleh kosong!')"
            oninput="this.setCustomValidity('')"
            <?php echo ($maintenance_mode && $role_login != 'admin') ? 'disabled' : ''; ?>></textarea>

        <label>FOTO BEFORE (KONDISI) :</label>
        <input type="file" name="foto_before" accept="image/*" capture="environment" required
            oninvalid="this.setCustomValidity('Tidak boleh kosong!')"
            oninput="this.setCustomValidity('')"
            <?php echo ($maintenance_mode && $role_login != 'admin') ? 'disabled' : ''; ?>>
        
        <input type="hidden" name="tanggal" value="<?php echo date('Y-m-d'); ?>">
        
        <button type="submit" name="simpan" <?php echo ($maintenance_mode && $role_login != 'admin') ? 'disabled' : ''; ?>>
            <?php echo ($maintenance_mode && $role_login != 'admin') ? 'MAINTENANCE AKTIF' : 'KIRIM'; ?>
        </button>
    </form>

    <div class="search-wrapper">
        <input type="text" id="keyword" class="input-search" placeholder="CARI DATA..." autocomplete="off">
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="30">NO</th>
                    <th>SECTION / DEPT</th>
                    <th>LOKASI</th>
                    <th>USER</th>
                    <th width="85">TANGGAL</th>
                    <th>KONDISI</th>
                    <th width="100">FOTO B/A</th>
                    <th width="100">STATUS</th>
                    <th width="120">AKSI</th>
                </tr>
            </thead>
            <tbody id="tabel-data">
                <?php
                $no = 1;
                $sql_init = "SELECT c.*, r.foto_after, r.ttd_user, r.ttd_pga
                    FROM complaints c
                    LEFT JOIN repair_actions r ON c.complain_id = r.complaint_id
                    ORDER BY c.complain_id DESC"; 
                $q_init = mysqli_query($conn, $sql_init);
                
                while($row = mysqli_fetch_array($q_init)){
                    $has_user = !empty($row['ttd_user']);
                    $has_pga  = !empty($row['ttd_pga']);
                    $is_done  = ($has_user && $has_pga);
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td class="caps"><?php echo htmlspecialchars($row['section_dept']); ?></td>
                    <td class="caps"><?php echo htmlspecialchars($row['lokasi_kerusakan']); ?></td>
                    <td class="caps"><?php echo htmlspecialchars($row['nama_user']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                    <td style="text-align: left;" class="caps">
                        <strong><?php echo nl2br(htmlspecialchars($row['kondisi_current'])); ?></strong>
                    </td>
                    <td>
                        <div class="img-container">
                            <?php if(!empty($row['foto_before'])): ?>
                                <img src="uploads/before/<?php echo $row['foto_before']; ?>" width="35" height="35" class="zoom-img" title="BEFORE" style="border-radius:3px; margin: 2px;">
                            <?php endif; ?>
                            <?php if(!empty($row['foto_after'])): ?>
                                <img src="uploads/after/<?php echo $row['foto_after']; ?>" width="35" height="35" class="zoom-img" style="border-radius:3px; margin: 2px;" title="AFTER">
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?php echo $is_done ? 'bg-selesai' : 'bg-proses'; ?>">
                            <?php echo $is_done ? 'SELESAI' : 'PROSES'; ?>
                        </span>
                        <div class="check-list">
                            <span class="<?php echo $has_user ? 'active' : ''; ?>">✔ USER</span>
                            <span class="<?php echo $has_pga ? 'active' : ''; ?>">✔ PGA</span>
                        </div>
                    </td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['complain_id']; ?>" class="btn-link">LIHAT</a>
                        <div style="margin-top: 8px;">
                            <?php if($has_pga): ?>
                                <a href="cetak.php?id=<?php echo $row['complain_id']; ?>" target="_blank" class="btn-pdf-enabled">PDF</a>
                            <?php else: ?>
                                <span class="btn-pdf-disabled" title="MENUNGGU TTD PGA">PDF</span>
                            <?php endif; ?>
                        </div>
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
    function closeWelcome() {
        const modal = document.getElementById('welcomeModal');
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    function openZoom(src) { $('#imgZoomed').attr('src', src); $('#imageModal').addClass('show'); }
    function closeZoom() { $('#imageModal').removeClass('show'); }

    $(document).ready(function(){
        let idleTime = 0;
        const keepAliveInterval = 30000; 
        let lastKeepAlive = Date.now();
        let idleInterval = setInterval(timerIncrement, 1000); 

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

        $(this).on('mousemove keypress mousedown touchstart scroll', function () { 
            idleTime = 0; 
            sendKeepAlive();
        });

        function timerIncrement() {
            idleTime++;
            if (idleTime >= 60) { 
                window.location.href = "logout.php?pesan=sesi_habis";
            }
        }

        $(document).on('click', '.zoom-img', function(){ openZoom($(this).attr('src')); });

        $('#keyword').on('keyup', function(){
            idleTime = 0; 
            sendKeepAlive();
            $.ajax({
                url: 'index.php',
                type: 'POST',
                data: { ajax_search: true, keyword: $(this).val() },
                success: function(response){
                    $('#tabel-data').html(response);
                }
            });
        });
    });
</script>
</body>
</html>