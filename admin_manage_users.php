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
    $sql = "SELECT * FROM users";
    
    if ($search != "") {
        $sql .= " WHERE nama_lengkap LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%'";
    }
    
    $q = mysqli_query($conn, $sql . " ORDER BY role DESC, nama_lengkap ASC");
    
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
                        <div><button type='button' class='btn-link' style='border:none; background:none; cursor:pointer; padding:0;' onclick=\"resetPassword('".$row['id']."', '".htmlspecialchars($row['username'])."')\">RESET PW</button></div>
                        <div style='margin-top: 8px;'>";
                        
                        if (!$isAdmin) {
                            echo "<a href='proses_hapus_user.php?id=".$row['id']."' class='btn-link btn-delete' onclick='return confirm(\"HAPUS AKUN ".htmlspecialchars(strtoupper($row['username']))."?\")'>HAPUS AKUN</a>";
                        } else {
                            echo "<span style='color: #888; font-size: 10px; font-weight: bold; border: 1px solid #444; padding: 2px 5px; border-radius: 3px;'>PROTECTED</span>";
                        }
                        
            echo "      </div>
                    </td>
                  </tr>";
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
        <a href="admin_dashboard_selesai.php" class="nav-link">SUDAH SELESAI</a>
        <a href="admin_manage_users.php" class="nav-link active-user">KELOLA USER</a>
    </div>

    <div class="search-wrapper">
        <p style="font-size: 12px; margin: 0; font-weight: bold; text-transform: uppercase; color: var(--text-color);">MENU: <strong style="color:#6c757d;">KELOLA DAFTAR USER</strong></p>
        <input type="text" id="keyword" class="input-search" placeholder="Cari nama, username, atau email..." autocomplete="off">
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
                $query = mysqli_query($conn, "SELECT * FROM users ORDER BY role DESC, nama_lengkap ASC");
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
                                <a href="proses_hapus_user.php?id=<?php echo $row['id']; ?>" class="btn-link btn-delete" onclick="return confirm('HAPUS AKUN <?php echo htmlspecialchars(strtoupper($row['username'])); ?>?')">HAPUS AKUN</a>
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
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="theme_script.js"></script>
<script>
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
            if (idleTime >= 60) { // 60 detik
                window.location.href = "logout.php?pesan=sesi_habis";
            }
        }
        // ---------------------------------

        $('#keyword').on('keyup', function(){
            idleTime = 0; 
            sendKeepAlive();
            $.ajax({
                url: 'admin_manage_users.php',
                type: 'POST',
                data: { ajax_search: true, keyword: $(this).val() },
                success: function(response){
                    $('#tabel-user').html(response);
                }
            });
        });
    });

    function resetPassword(id, username) {
        let newPass = prompt("MASUKKAN PASSWORD BARU UNTUK " + username.toUpperCase() + ":", "Admin123");
        if (newPass !== null && newPass.trim() !== "") {
            if (confirm("RUBAH PASSWORD " + username.toUpperCase() + " MENJADI: " + newPass + "?")) {
                window.location.href = "proses_reset_user.php?id=" + id + "&pass=" + encodeURIComponent(newPass);
            }
        }
    }
</script>
</body>
</html>