<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['status']) || $_SESSION['role'] != "admin"){
    header("location:admin_dashboard_proses.php"); 
    exit;
}

$role_login = $_SESSION['role'];
$nama_login = $_SESSION['nama'];

if (isset($_POST['ajax_search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['keyword']);
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 5;
    $sql = "SELECT * FROM users";
    
    if ($search != "") {
        $sql .= " WHERE nama_lengkap LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%'";
    }
    
    $q = mysqli_query($conn, $sql . " ORDER BY role DESC, nama_lengkap ASC LIMIT $limit");
    
    if (mysqli_num_rows($q) > 0) {
        $no = 1;
        while ($row = mysqli_fetch_array($q)) {
            $isAdmin = ($row['role'] == 'admin');
            echo "<tr>
                    <td>".$no++."</td>
                    <td class='username-bold'>".htmlspecialchars($row['username'])."</td>
                    <td class='caps'>".htmlspecialchars($row['nama_lengkap'])."</td>
                    <td style='text-transform: lowercase;'>".htmlspecialchars($row['email'])."</td>
                    <td><span class='badge-role caps'>".$row['role']."</span></td>
                    <td style='color: #999; font-family: monospace; font-size: 11px;'>
                        ".($row['device_id'] ? substr($row['device_id'], 0, 15).'...' : '-')."
                    </td>
                    <td>
                        <div><button type='button' class='btn-link' style='border:none; background:none; cursor:pointer; padding:0;' 
                        onclick=\"resetPassword('".$row['id']."', '".htmlspecialchars($row['username'])."')\">RESET PW</button></div>
                        <div style='margin-top: 8px;'>";
                        
                        if (!$isAdmin) {
                            echo "<a href='proses_hapus_user.php?id=".$row['id']."' 
                            class='btn-link btn-delete alert-delete' 
                            data-id='".$row['id']."'data-username='".htmlspecialchars($row['username'])."'>HAPUS AKUN</a>";
                        } else {
                            echo "<span style='color: #888; font-size: 10px; font-weight: bold; border: 1px solid #444; padding: 2px 5px; border-radius: 3px;'>PROTECTED</span>";
                        }
                        
            echo "      </div>
                    </td>
                  </tr>";
        }
        if (mysqli_num_rows($q) >= $limit) {
            echo "";
        }
    } else {
        echo "<tr><td colspan='7' class='caps'>DATA TIDAK DITEMUKAN.</td></tr>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN - KELOLA USER</title>
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
            font-size: 12px; 
            font-weight: bold; 
            border-radius: 5px 5px 0 0; 
            border: 1px solid transparent; 
            transition: all 0.3s ease; 
        }
        .nav-link.active-user { 
            background: #6c757d; 
            color: #fff; 
            border-color: var(--border-color) var(--border-color) transparent; 
        }
        .nav-link:hover:not(.active-user) { 
            background: rgba(128,128,128,0.2); 
            transform: translateY(-1px); 
        }
        .search-wrapper { 
            display: flex; 
            flex-direction: column; 
            gap: 10px; 
            margin-bottom: 15px; 
        }
        .search-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }
        .btn-add-account {
            background-color: #28a745; 
            background-image: linear-gradient(90deg, #28a745, #28a745);
            background-size: 100% 100%;
            color: white; 
            padding: 10px 18px; 
            font-size: 13px;
            font-weight: bold;
            border-radius: 8px; 
            cursor: pointer;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 8px; 
            white-space: nowrap; 
            text-transform: uppercase;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); 
            border: none; 
            position: relative;
            z-index: 1;
            overflow: hidden;
            box-shadow: 0 4px 15 rgba(40, 167, 69, 0.3);
            width: 100%; 
            box-sizing: border-box;
        }

        .btn-add-account:hover { 
            transform: translateY(-3px);
            background-image: linear-gradient(90deg, #28a745, #00ff88, #2ecc71, #28a745);
            background-size: 200% 100%;
            animation: auroraMove 2s linear infinite;
            box-shadow: 0 8px 25px rgba(0, 255, 136, 0.5), 0 0 40px rgba(40, 167, 69, 0.3);
            color: white; 
        }
        @keyframes auroraMove {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
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
            border: 1px solid #6c757d;
            box-shadow: 0 0 5px rgba(108,117,125,0.2); 
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
        .username-bold { 
            font-weight: bold; 
            color: var(--text-color); 
        }
        .badge-role { 
            background: rgba(128,128,128,0.1); 
            color: var(--text-color); 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 10px; 
            font-weight: bold; 
            border: 1px solid var(--border-color); 
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
            transform: scale(1.1); 
            color: #0056b3; 
        }
        .btn-delete { 
            color: #dc3545; 
        }
        .btn-delete:hover { 
            color: #a71d2a !important; 
        }
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
            display: none; 
        }
        .btn-show-more:hover {
            background: #555;
            color: #fff;
            border-color: #888;
        }
        .swal2-input-custom {
            width: 80% !important;
            padding: 10px !important;
            margin: 10px auto !important;
            box-sizing: border-box;
            background: var(--input-bg) !important;
            color: var(--text-color) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 4px !important;
        }
        .swal-modern-popup {
            border-radius: 12px !important;
            padding: 20px !important;
            width: 450px !important; 
        }

        .swal-form-container {
            display: flex;
            flex-direction: column;
            gap: 14px; 
            margin-top: 15px;
            text-align: left; 
        }

        .swal-input-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .swal-input-group label {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--text-color);
            opacity: 0.9;
        }

        .swal-input-group label i {
            margin-right: 5px;
            width: 15px;
            text-align: center;
        }

        .swal-custom-field {
            width: 100% !important;
            box-sizing: border-box;
            padding: 10px 12px !important;
            font-size: 14px !important;
            background: var(--input-bg) !important;
            color: var(--text-color) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 6px !important;
            margin: 0 !important; 
            outline: none;
            transition: all 0.3s ease;
        }

        .swal-custom-field:focus {
            border-color: #28a745 !important; 
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.3) !important;
        }

        select.swal-custom-field {
            cursor: pointer;
            height: 38px;
        }
        
        .swal2-confirm.swal2-styled {
            background-color: #28a745 !important;
            background-image: linear-gradient(90deg, #28a745, #28a745) !important;
            background-size: 100% 100% !important;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3) !important;
        }
        .swal2-confirm.swal2-styled:hover {
            background-image: linear-gradient(90deg, #28a745, #00ff88, #2ecc71, #28a745) !important;
            background-size: 200% 100% !important;
            animation: auroraMove 2s linear infinite !important;
            box-shadow: 0 8px 25px rgba(0, 255, 136, 0.5), 0 0 40px rgba(40, 167, 69, 0.3) !important;
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
                display: flex;
                gap: 10px;
            }
            .btn-add-account {
                width: auto; 
            }
            .input-search { 
                width: 350px; 
            }
            .nav-link { 
                flex: none; 
                padding: 10px 25px; 
                font-size: 13px; 
            }
            .theme-switcher {
                position: fixed;
                bottom: 25px;
                left: 25px;
                z-index: 1000;
            }
        }
        .btn-logout[style*="#007bff"]:hover {
            background: #007bff !important;
            color: #fff !important;
            box-shadow: 0 3px 8px rgba(38, 0, 255, 0.63) !important;
        }
        body.swal2-shown {
            overflow-y: scroll !important;
            padding-right: 0 !important;
        }
        .swal2-popup {
            background: var(--container-bg) !important;
            color: var(--text-color) !important;
            border: 1px solid var(--border-color);
        }
        .swal2-title, .swal2-html-container {
            color: var(--text-color) !important;
        }
        body.swal2-shown {
            overflow: hidden !important;
            padding-right: 0 !important;
        }
        html.swal2-shown {
            overflow: hidden !important;
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

<div class="container">
    <div class="header-section">
        <img src="bahan/logo.png" alt="Logo" class="logo-img" onerror="this.src='https://via.placeholder.com/150x50?text=SHINSEI'">
        <div>
            <h2>ADMIN MONITORING</h2>
            <div class="user-info">
                HALO, <strong><?php echo htmlspecialchars(strtoupper($nama_login)); ?></strong>
                <a href="logout.php" class="btn-logout alert-logout">KELUAR</a>

                <?php if($role_login === 'admin'): ?>
                    <a href="index.php" class="btn-logout" style="color: #007bff; border-color: #007bff; margin-right: 5px;">USER</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="nav-tabs">
        <a href="admin_dashboard_proses.php" class="nav-link">MASIH PROSES</a>
        <a href="admin_dashboard_selesai.php" class="nav-link">SUDAH SELESAI</a>
        <a href="admin_manage_users.php" class="nav-link active-user">KELOLA USER</a>
    </div>

    <div class="search-wrapper">
        <p style="font-size: 12px; margin: 0; font-weight: bold; text-transform: uppercase; color: var(--text-color);">MENU: <strong style="color:#6c757d;">KELOLA DAFTAR USER</strong></p>
        <div class="search-controls">
            <button type="button" class="btn-add-account" onclick="addAccountPopUp()">
                <i class="fa-solid fa-user-plus"></i> ADD ACCOUNT
            </button>
            <input type="text" id="keyword" class="input-search" placeholder="Cari nama, username, atau email..." autocomplete="off">
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="30">NO</th>
                    <th>USERNAME</th>
                    <th>NAMA LENGKAP</th>
                    <th>EMAIL</th>
                    <th width="80">ROLE</th>
                    <th width="120">DEVICE ID</th>
                    <th width="120">AKSI</th>
                </tr>
            </thead>
            <tbody id="tabel-user">
                <?php
                $no = 1;
                $query = mysqli_query($conn, "SELECT * FROM users ORDER BY role DESC, nama_lengkap ASC LIMIT 5");
                $count_row = mysqli_num_rows($query);
                while($row = mysqli_fetch_array($query)){
                    $isAdmin = ($row['role'] == 'admin');
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td class="username-bold"><?php echo htmlspecialchars($row['username']); ?></td>
                    <td class="caps"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                    <td style="text-transform: lowercase;"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><span class="badge-role caps"><?php echo $row['role']; ?></span></td>
                    <td style="color: #999; font-family: monospace; font-size: 11px;">
                        <?php echo $row['device_id'] ? substr($row['device_id'], 0, 15).'...' : '-'; ?>
                    </td>
                    <td>
                        <div>
                            <button type="button" class="btn-link" style="border:none; background:none; cursor:pointer; padding:0;" onclick="resetPassword('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['username']); ?>')">RESET PW</button>
                        </div>
                        <div style="margin-top: 8px;">
                            <?php if (!$isAdmin): ?>
                                <a href="proses_hapus_user.php?id=<?php echo $row['id']; ?>" 
                                class="btn-link btn-delete alert-delete" data-id="<?php echo $row['id']; ?>" 
                                data-username="<?php echo htmlspecialchars($row['username']); ?>">HAPUS AKUN</a>
                            <?php else: ?>
                                <span style="color: #888; font-size: 10px; font-weight: bold; border: 1px solid #444; padding: 2px 5px; border-radius: 3px;">PROTECTED</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="show-more-wrapper">
        <button type="button" id="btn-show-more" class="btn-show-more" 
        style="display: <?php echo ($count_row >= 5) ? 'inline-block' : 'none'; ?>;">
            TAMPILKAN LEBIH BANYAK
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="theme_script.js"></script>
<script>
    
    $(document).ready(function(){
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'update_success') {
        window.history.replaceState({}, document.title, window.location.pathname);
        Swal.fire({
            title: 'BERHASIL!',
            text: 'Password telah diperbarui.',
            icon: 'success',
            confirmButtonColor: '#28a745',
            confirmButtonText: 'OKE',
            scrollbarPadding: false,
            heightAuto: false
        });
    }

    if (urlParams.get('status') === 'add_success') {
        window.history.replaceState({}, document.title, window.location.pathname);
        Swal.fire({
            title: 'BERHASIL!',
            text: 'Akun baru telah ditambahkan.',
            icon: 'success',
            confirmButtonColor: '#28a745',
            confirmButtonText: 'OKE',
            scrollbarPadding: false,
            heightAuto: false
        });
    }

        let currentLimit = 5;
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

        function loadData(limit) {
            let keyword = $('#keyword').val();
            $.ajax({
                url: 'admin_manage_users.php',
                type: 'POST',
                data: { 
                    ajax_search: true, 
                    keyword: keyword,
                    limit: limit 
                },
                success: function(response){
                    $('#tabel-user').html(response);
                    if (response.indexOf("") !== -1) {
                        $('#btn-show-more').show();
                    } else {
                        $('#btn-show-more').hide();
                    }
                }
            });
        }
        

        $('#keyword').on('keyup', function(){
            currentLimit = 5;
            idleTime = 0; 
            sendKeepAlive();
            loadData(currentLimit);
        });

        $('#btn-show-more').on('click', function(){
            currentLimit += 5;
            loadData(currentLimit);
        });
    });
    
    $(document).on('click', '.alert-logout', function(e){
        e.preventDefault(); 
        const url = $(this).attr('href');
        
        Swal.fire({
            title: 'YAKIN INGIN KELUAR?',
            text: "Sesi Anda akan diakhiri.",
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'YA, KELUAR',
            cancelButtonText: 'BATAL',
            scrollbarPadding: false, 
            heightAuto: false        
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
    const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
});
$(document).on('click', '.alert-delete', function(e) {
    e.preventDefault();
    
    const btn = $(this);
    const id = btn.data('id');
    const username = btn.data('username') ? btn.data('username').toUpperCase() : 'USER';
    const row = btn.closest('tr'); 

    Swal.fire({
        title: 'HAPUS AKUN?',
        text: "Apakah Anda yakin ingin menghapus akun " + username + "?",
        icon: 'warning',
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'YA, HAPUS',
        cancelButtonText: 'BATAL',
        scrollbarPadding: false,
        heightAuto: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'proses_hapus_user.php',
                type: 'GET',
                data: { id: id },
                success: function(response) {
                    row.fadeOut(400, function() { 
                        $(this).remove(); 
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'AKUN ' + username + ' BERHASIL DIHAPUS'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'GAGAL',
                        text: 'Terjadi kesalahan sistem.',
                        heightAuto: false
                    });
                }
            });
        }
    });
});

    function resetPassword(id, username) {
        Swal.fire({
            title: 'RESET PASSWORD',
            text: "Masukkan password baru untuk " + username.toUpperCase(),
            input: 'text',
            inputValue: 'Admin123',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: 'SIMPAN',
            cancelButtonText: 'BATAL',
            confirmButtonColor: '#6c7d6c', 
            cancelButtonColor: '#444',
            scrollbarPadding: false,
            heightAuto: false,
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const newPass = result.value;
                Swal.fire({
                    title: 'KONFIRMASI',
                    text: "Ubah password " + username.toUpperCase() + " menjadi: " + newPass + "?",
                    icon: 'question',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: 'YA, UBAH',
                    cancelButtonText: 'BATAL',
                    confirmButtonColor: '#6c757d',
                    cancelButtonColor: '#444'
                }).then((confirmResult) => {
                    if (confirmResult.isConfirmed) {
                        window.location.href = "proses_reset_user.php?id=" + id + "&pass=" + encodeURIComponent(newPass);
                    }
                });
            }
        });
    }

    function addAccountPopUp() {
    Swal.fire({
        title: '<i class="fa-solid fa-user-plus" style="color: #28a745; margin-right: 10px;"></i>TAMBAH AKUN BARU',
        html: `
            <div class="swal-form-container">
                <div class="swal-input-group">
                    <label><i class="fa-solid fa-user"></i> Username</label>
                    <input type="text" id="new_username" class="swal-custom-field" placeholder="Masukkan username" autocomplete="off" required>
                </div>
                <div class="swal-input-group">
                    <label><i class="fa-solid fa-id-card"></i> Nama Lengkap</label>
                    <input type="text" id="new_nama_lengkap" class="swal-custom-field" placeholder="Masukkan nama lengkap" autocomplete="off" required>
                </div>
                <div class="swal-input-group">
                    <label><i class="fa-solid fa-envelope"></i> Email (Opsional)</label>
                    <input type="email" id="new_email" class="swal-custom-field" placeholder="user@shinsei-denshi.id" autocomplete="off">
                </div>
                <div class="swal-input-group">
                    <label><i class="fa-solid fa-lock"></i> Password</label>
                    <input type="password" id="new_password" class="swal-custom-field" placeholder="••••••••" autocomplete="off" required>
                </div>
                <div class="swal-input-group">
                    <label><i class="fa-solid fa-user-shield"></i> Role / Hak Akses</label>
                    <select id="new_role" class="swal-custom-field">
                        <option value="teknisi">TEKNISI</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonText: 'SIMPAN',
        cancelButtonText: 'BATAL',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        scrollbarPadding: false,
        heightAuto: false,
        customClass: {
            popup: 'swal-modern-popup'
        },
        preConfirm: () => {
            const username = Swal.getPopup().querySelector('#new_username').value.trim();
            const nama_lengkap = Swal.getPopup().querySelector('#new_nama_lengkap').value.trim();
            const email = Swal.getPopup().querySelector('#new_email').value.trim();
            const password = Swal.getPopup().querySelector('#new_password').value.trim();
            const role = Swal.getPopup().querySelector('#new_role').value;

            if (!username || !nama_lengkap || !password) {
                Swal.showValidationMessage(`Harap lengkapi seluruh form data wajib (Username, Nama Lengkap, & Password)!`);
            }
            return { username: username, nama_lengkap: nama_lengkap, email: email, password: password, role: role }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const data = result.value;                
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'proses_tambah_user.php';

            for (const key in data) {
                if (data.hasOwnProperty(key)) {
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = key;
                    hiddenField.value = data[key];
                    form.appendChild(hiddenField);
                }
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
</body>
</html>