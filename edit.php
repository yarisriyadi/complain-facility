<?php 
session_start();

include 'config_maintenance.php';
cek_akses_maintenance($maintenance_mode);

if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("location:login.php");
    exit;
}

include 'koneksi.php';

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
$role_login = $_SESSION['role'];
$nama_login = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'];

$sql = "SELECT c.*, r.repair_action, r.foto_after, r.ttd_user, r.ttd_pga 
        FROM complaints c 
        LEFT JOIN repair_actions r ON c.complain_id = r.complaint_id 
        WHERE c.complain_id='$id'";
$result = mysqli_query($conn, $sql);
$d = mysqli_fetch_array($result);

if(!$d){
    echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

if ($role_login == 'admin' || $role_login == 'superadmin' || $role_login == 'teknisi' || $role_login == 'pga') {
    if (!empty($d['ttd_user']) && !empty($d['ttd_pga'])) {
        $back_link = 'admin_dashboard_selesai.php';
    } else {
        $back_link = 'admin_dashboard_proses.php';
    }
} else {
    $back_link = 'index.php';
}

$can_edit_repair = ($role_login == 'teknisi' || $role_login == 'admin' || $role_login == 'superadmin');
$can_sign_user = ($role_login == 'user' || $role_login == 'admin' || $role_login == 'superadmin');
$can_sign_pga  = ($role_login == 'pga' || $role_login == 'admin' || $role_login == 'superadmin');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHINSEI - EDIT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style_theme.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    
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
        padding: 25px; 
        max-width: 800px; 
        margin: auto; 
        border-radius: 8px; 
        box-shadow: 0 4px 15px var(--shadow); 
    }
    form { 
        background: rgba(128, 128, 128, 0.05); 
        padding: 20px; 
        border: 1px solid var(--border-color); 
        border-radius: 6px; 
    }
    label { 
        display: block; 
        margin-top: 15px; 
        margin-bottom: 5px; 
        font-weight: bold; 
        font-size: 13px; 
        text-transform: uppercase; 
    }
    
    textarea, input[type="file"] { 
        width: 100%; 
        padding: 12px; 
        margin: 5px 0; 
        border: 1px solid var(--border-color); 
        border-radius: 4px; 
        background: var(--input-bg); 
        color: var(--input-text); /* Menggunakan variabel warna teks input */
        font-family: Arial, sans-serif; 
        font-size: 14px; 
        box-sizing: border-box; 
    }

    .readonly-field { 
        background: rgba(128, 128, 128, 0.1) !important; 
        opacity: 0.7; 
        color: var(--text-color) !important;
        cursor: not-allowed; 
    }

    .img-preview { 
        display: block; 
        margin: 10px 0; 
        border-radius: 4px; 
        border: 1px solid var(--border-color); 
        width: 200px; 
        height: 200px; 
        object-fit: cover; 
        background: #fff; 
        padding: 3px; 
    }

    .signature-section { 
        display: grid; 
        grid-template-columns: 1fr; 
        gap: 20px; 
        margin-top: 25px; 
    }
    .sig-wrapper { 
        border: 2px dashed var(--border-color); 
        width: 100%; 
        height: 180px; 
        background: #fff;
        position: relative; 
        border-radius: 4px; 
        overflow: hidden; 
        touch-action: none; 
    }
    canvas { 
        width: 100%; 
        height: 100%; 
        cursor: crosshair; 
    }

    .btn-clear { 
        font-size: 11px; 
        padding: 8px; 
        background: #dc3545; 
        color: white; 
        border: none; 
        cursor: pointer; 
        margin-top: 8px; 
        border-radius: 3px; 
        width: 100%; 
        text-transform: uppercase; 
        transition: 0.3s;
    }
    .btn-clear:hover { 
    background: #a71d2a; 
    }

    .action-group { 
        margin-top: 30px; 
        display: flex; 
        flex-direction: column; 
        gap: 15px; 
    }

    .btn-update, .btn-back { 
        padding: 15px; 
        border: none; 
        cursor: pointer; 
        border-radius: 6px; 
        font-weight: bold; 
        font-size: 15px; 
        transition: all 0.3s ease; 
        text-transform: uppercase; 
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-update { 
        background: #28a745; 
        color: white; 
    }

    .btn-update:hover { 
        background: #218838; 
        transform: translateY(-3px); 
    }
    .btn-back { 
    background: #6c757d;   
    color: white; 
    }
    .btn-back:hover { 
    background: #5a6268; 
    transform: translateY(-3px); 
}

    .disabled-pad { 
    background-color: #f0f0f0 !important; 
    cursor: not-allowed; 
    opacity: 0.5; 
}

    @media (min-width: 768px) { 
        .signature-section { 
        grid-template-columns: 1fr 1fr; 
    } 
        .action-group { 
        flex-direction: row; 
        justify-content: center; 
    } 
        .btn-update, .btn-back { 
        width: 220px; 
    }
        .btn-update { 
        order: 2; 
    } .btn-back { 
        order: 1; 
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
    <form action="proses.php" method="POST" enctype="multipart/form-data" id="formUpdate">
        <input type="hidden" name="id" value="<?php echo $d['complain_id']; ?>">
        
        <label>Repair Action (Tindakan Perbaikan) :</label>
        <textarea name="repair_action" id="repair_action" 
            <?php echo (!$can_edit_repair) ? 'readonly class="readonly-field"' : 'required'; ?> 
            placeholder="Teknisi wajib mengisi detail perbaikan di sini..."><?php echo htmlspecialchars($d['repair_action'] ?? ''); ?></textarea>
        
        <label>Foto After (Bukti Selesai) :</label>
        <?php if(!empty($d['foto_after'])): ?>
            <img src="uploads/after/<?php echo $d['foto_after']; ?>" class="img-preview" alt="Foto After">
        <?php endif; ?>

        <?php if($can_edit_repair): ?>
            <input type="file" name="foto_after" id="foto_after" accept="image/*" capture="environment"
                <?php echo (empty($d['foto_after'])) ? 'required' : ''; ?>>
            <small style="opacity: 0.7; display: block; margin-top: 5px;">* Khusus Teknisi: Upload foto hasil perbaikan.</small>
        <?php elseif($role_login == 'user'): ?>
            <div style="font-size: 12px; color: #666; font-style: italic; border-left: 3px solid #ccc; padding-left: 10px; margin-top: 5px;">
                Catatan: Foto perbaikan diunggah oleh teknisi.
            </div>
        <?php endif; ?>

        <div class="signature-section">
            <div class="sig-container">
                <label>TTD USER (PELAPOR)</label>
                <div class="sig-wrapper <?php echo (!$can_sign_user) ? 'disabled-pad' : ''; ?>">
                    <canvas id="pad-user"></canvas>
                </div>
                <input type="hidden" name="ttd_user" id="in-user" value="<?php echo htmlspecialchars($d['ttd_user'] ?? ''); ?>">
                <?php if($can_sign_user): ?>
                    <button type="button" class="btn-clear" id="clear-user">Hapus TTD User</button>
                <?php endif; ?>
            </div>

            <div class="sig-container">
                <label>TTD PGA (VERIFIKASI)</label>
                <div class="sig-wrapper <?php echo (!$can_sign_pga) ? 'disabled-pad' : ''; ?>">
                    <canvas id="pad-pga"></canvas>
                </div>
                <input type="hidden" name="ttd_pga" id="in-pga" value="<?php echo htmlspecialchars($d['ttd_pga'] ?? ''); ?>">
                <?php if($can_sign_pga): ?>
                    <button type="button" class="btn-clear" id="clear-pga">Hapus TTD PGA</button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="action-group">
            <a href="<?php echo $back_link; ?>" class="btn-back">KEMBALI</a>
            <button type="submit" name="update" class="btn-update">SIMPAN PERUBAHAN</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="theme_script.js"></script><script>
    // 1. Inisialisasi Signature Pad
    const canvasUser = document.getElementById('pad-user');
    const canvasPga = document.getElementById('pad-pga');
    const pUser = new SignaturePad(canvasUser);
    const pPga = new SignaturePad(canvasPga);

    // --- FITUR AUTO LOGOUT & KEEP ALIVE ---
    const iTimeout = 60 * 1000; // 1 Menit
    let iTimer;
    const keepAliveInterval = 30000; // Ping server setiap 30 detik
    let lastKeepAlive = Date.now();

    function startTimer() {
        clearTimeout(iTimer);
        iTimer = setTimeout(() => {
            window.location.href = 'logout.php?pesan=sesi_habis';
        }, iTimeout);
    }

    function sendKeepAlive() {
        let now = Date.now();
        if (now - lastKeepAlive > keepAliveInterval) {
            fetch('keep_alive.php')
                .then(() => {
                    console.log("Sesi diperbarui");
                    lastKeepAlive = now;
                })
                .catch(err => console.warn("Gagal sinkronisasi sesi"));
        }
    }

    function resetTimer() {
        startTimer();
        sendKeepAlive(); // Beritahu server user masih aktif
    }
    // ------------------------------------

    function resizeCanvas() {
        [canvasUser, canvasPga].forEach(canvas => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const pad = (canvas.id === 'pad-user') ? pUser : pPga;
            const data = pad.toData();
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            pad.fromData(data);
        });
    }

    window.addEventListener("resize", resizeCanvas);
    
    // Matikan pad jika role tidak diizinkan
    <?php if(!$can_sign_user) echo "pUser.off();"; ?>
    <?php if(!$can_sign_pga) echo "pPga.off();"; ?>

    window.addEventListener("load", () => {
        resizeCanvas(); 
        startTimer();
        
        const dataUser = `<?php echo $d['ttd_user']; ?>`;
        const dataPga = `<?php echo $d['ttd_pga']; ?>`;
        if (dataUser && dataUser !== "") pUser.fromDataURL(dataUser);
        if (dataPga && dataPga !== "") pPga.fromDataURL(dataPga);
    });

    // Deteksi Aktivitas Umum
    window.onmousemove = resetTimer;
    window.onmousedown = resetTimer; 
    window.ontouchstart = resetTimer;
    window.onclick = resetTimer;
    window.onkeydown = resetTimer;
    window.addEventListener('scroll', resetTimer, true);
    
    // Reset timer & Keep Alive saat user tanda tangan (Sangat Penting!)
    canvasUser.addEventListener('mousedown', resetTimer);
    canvasPga.addEventListener('mousedown', resetTimer);
    canvasUser.addEventListener('touchstart', resetTimer);
    canvasPga.addEventListener('touchstart', resetTimer);

    // Fungsi Clear TTD
    const setupClear = (btnId, pad, inputId) => {
        const btn = document.getElementById(btnId);
        if(btn) {
            btn.onclick = () => {
                resetTimer(); 
                if(confirm("Hapus tanda tangan ini?")) {
                    pad.clear();
                    document.getElementById(inputId).value = "";
                }
            };
        }
    };

    setupClear('clear-user', pUser, 'in-user');
    setupClear('clear-pga', pPga, 'in-pga');

    // Validasi saat Submit
    document.getElementById('formUpdate').onsubmit = function(e) {
        const inputUser = document.getElementById('in-user');
        const inputPga = document.getElementById('in-pga');
        const role = "<?php echo $role_login; ?>";

        if (!pUser.isEmpty()) inputUser.value = pUser.toDataURL();
        if (!pPga.isEmpty()) inputPga.value = pPga.toDataURL();

        if (role === 'user') {
            if (pUser.isEmpty() && inputUser.value === "") {
                alert("Anda wajib membubuhkan tanda tangan USER sebagai konfirmasi!");
                e.preventDefault(); return false;
            }
        }

        if (role === 'pga') {
            if (pPga.isEmpty() && inputPga.value === "") {
                alert("Anda wajib membubuhkan tanda tangan PGA untuk verifikasi!");
                e.preventDefault(); return false;
            }
        }
    };
</script>
</body>
</html>