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
        color: var(--input-text); 
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
        width: 100% !important; 
        height: 100% !important; 
        cursor: crosshair; 
        display: block;
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
        background: #25d366; 
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
    .modal-tek {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0; width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.7);
        backdrop-filter: blur(8px);
        align-items: center; 
        justify-content: center;
    }
    .modal-tek-content {
        background-color: var(--container-bg);
        padding: 30px;
        border-radius: 16px;
        width: 90%;
        max-width: 380px;
        text-align: center;
        border: 1px solid var(--border-color);
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        position: relative;
        animation: modalShow 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes modalShow {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .btn-tek-choice {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
        padding: 14px;
        margin: 12px 0;
        border: none;
        border-radius: 10px;
        background: #25d366;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
        font-size: 15px;
        box-shadow: 0 4px 10px rgba(37, 211, 102, 0.2);
    }
    .btn-tek-choice:hover { 
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(37, 211, 102, 0.3);
        filter: brightness(1.1);
    }
    .btn-tek-choice i { font-size: 20px; 
    }
    .modal-hint {
        margin-top: 20px;
        font-size: 11px;
        opacity: 0.5;
        font-style: italic;
    }
    @media (min-width: 768px) { 
        .signature-section { grid-template-columns: 1fr 1fr; } 
        .action-group { flex-direction: row; justify-content: center; } 
        .btn-update, .btn-back { width: 220px; }
        .btn-update { order: 2; } 
        .btn-back { order: 1; }
    }
</style>
</head>
<body data-theme="dark">

<div id="modalTeknisi" class="modal-tek" onclick="closeModal()">
    <div class="modal-tek-content" onclick="event.stopPropagation()">
        <div style="margin-bottom: 20px;">
            <i class="fa-solid fa-paper-plane" style="font-size: 40px; color: #25d366;"></i>
        </div>
        <h3 style="margin-bottom: 10px; letter-spacing: 0.5px;">Kirim Laporan</h3>
        <p style="font-size: 13px; margin-bottom: 25px; opacity: 0.8; line-height: 1.5;">
            Pilih teknisi untuk notifikasi perbaikan melalui WhatsApp</p>
        
        <button type="button" class="btn-tek-choice" onclick="submitWithWA('6285881568007')">
            <i class="fa-brands fa-whatsapp"></i> Pak Martani
        </button>
        <button type="button" class="btn-tek-choice" onclick="submitWithWA('6285777347355')">
            <i class="fa-brands fa-whatsapp"></i> Mas Dodik
        </button>

        <p class="modal-hint">Klik di luar kotak ini untuk membatalkan</p>
    </div>
</div>

<div class="theme-switcher">
    <button class="theme-btn" onclick="toggleTheme()" title="Ganti Tema">
        <i id="theme-icon-sun" class="fa-solid fa-sun" style="color: #f1c40f;"></i>
        <i id="theme-icon-moon" class="fa-solid fa-moon" style="color: #f1c40f;"></i>
    </button>
</div>

<div class="container">
    <form action="proses.php" method="POST" enctype="multipart/form-data" id="formUpdate">
        <input type="hidden" name="id" value="<?php echo $d['complain_id']; ?>">
        <input type="hidden" name="wa_teknisi" id="wa_teknisi" value="">
        
        <label>Repair Action (Tindakan Perbaikan) :</label>
        <textarea name="repair_action" id="repair_action" 
            <?php echo (!$can_edit_repair) ? 'readonly class="readonly-field"' : 'required'; ?>
            oninvalid="this.setCustomValidity('Tidak boleh kosong!')"
            oninput="this.setCustomValidity('')" 
            placeholder="Teknisi wajib mengisi detail perbaikan di sini..."><?php echo htmlspecialchars($d['repair_action'] ?? ''); ?></textarea>
        
        <label>Foto After (Bukti Selesai) :</label>
        <?php if(!empty($d['foto_after'])): ?>
            <img src="uploads/after/<?php echo $d['foto_after']; ?>" class="img-preview" alt="Foto After">
        <?php endif; ?>

        <?php if($can_edit_repair): ?>
            <input type="file" name="foto_after" id="foto_after" accept="image/*" capture="environment"
            oninvalid="this.setCustomValidity('Tidak boleh kosong!')"
            oninput="this.setCustomValidity('')"
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
            <button type="submit" name="update" class="btn-update">SIMPAN</button>
            <a href="<?php echo $back_link; ?>" class="btn-back">KEMBALI</a>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="theme_script.js"></script>
<script>
    const canvasUser = document.getElementById('pad-user');
    const canvasPga = document.getElementById('pad-pga');
    const pUser = new SignaturePad(canvasUser);
    const pPga = new SignaturePad(canvasPga);

    const iTimeout = 60 * 1000; 
    const refreshInterval = 10 * 1000;
    const keepAliveInterval = 30000;

    let iTimer;
    let autoRefreshTimer; 
    let lastKeepAlive = Date.now();

    function startTimer() {
        clearTimeout(iTimer);
        iTimer = setTimeout(() => {
            window.location.href = 'logout.php?pesan=sesi_habis';
        }, iTimeout);
    }

   function startAutoRefresh() {
        clearTimeout(autoRefreshTimer);
        autoRefreshTimer = setTimeout(() => {
            if (pUser.isEmpty() && pPga.isEmpty()) {
                location.reload();
            } else {
                startAutoRefresh();
            }
        }, refreshInterval);
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
        startAutoRefresh();
        sendKeepAlive(); 
    }

    function resizeCanvas() {
        [canvasUser, canvasPga].forEach(canvas => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const pad = (canvas.id === 'pad-user') ? pUser : pPga;
            const data = pad.toData();
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            const ctx = canvas.getContext("2d");
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(ratio, ratio);
            pad.clear();
            pad.fromData(data);
        });
    }

    function closeModal() {
        document.getElementById('modalTeknisi').style.display = 'none';
    }

    function submitWithWA(no) {
        document.getElementById('wa_teknisi').value = no;
        const inputUser = document.getElementById('in-user');
        const inputPga = document.getElementById('in-pga');
        if (!pUser.isEmpty()) inputUser.value = pUser.toDataURL();
        if (!pPga.isEmpty()) inputPga.value = pPga.toDataURL();
        
        closeModal();
        document.getElementById('formUpdate').submit();
    }

    window.addEventListener("resize", resizeCanvas);
    
    <?php if(!$can_sign_user) echo "pUser.off();"; ?>
    <?php if(!$can_sign_pga) echo "pPga.off();"; ?>

    window.addEventListener("load", () => {
        resizeCanvas(); 
        startTimer();
        startAutoRefresh(); 
        
        const dataUser = `<?php echo $d['ttd_user']; ?>`;
        const dataPga = `<?php echo $d['ttd_pga']; ?>`;
        if (dataUser && dataUser !== "") pUser.fromDataURL(dataUser);
        if (dataPga && dataPga !== "") pPga.fromDataURL(dataPga);
    });

    window.onmousemove = resetTimer;
    window.onmousedown = resetTimer; 
    window.ontouchstart = resetTimer;
    window.onclick = resetTimer;
    window.onkeydown = resetTimer;
    window.addEventListener('scroll', resetTimer, true);
    
    canvasUser.addEventListener('mousedown', resetTimer);
    canvasPga.addEventListener('mousedown', resetTimer);
    canvasUser.addEventListener('touchstart', resetTimer);
    canvasPga.addEventListener('touchstart', resetTimer);

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

    document.getElementById('formUpdate').onsubmit = function(e) {
        const inputUser = document.getElementById('in-user');
        const inputPga = document.getElementById('in-pga');
        const role = "<?php echo $role_login; ?>"; 

        if (!pUser.isEmpty()) inputUser.value = pUser.toDataURL();
        if (!pPga.isEmpty()) inputPga.value = pPga.toDataURL();

        const isUserEmpty = (pUser.isEmpty() && inputUser.value === "");
        const isPgaEmpty = (pPga.isEmpty() && inputPga.value === "");

        if (role === 'teknisi') {
            return true; 
        }

        if (role === 'admin' || role === 'superadmin') {
            if (isUserEmpty && isPgaEmpty) {
                alert("Kedua tanda tangan tidak boleh kosong!");
                e.preventDefault(); return false;
            }
            else if (isPgaEmpty) {
                alert("Anda wajib tanda tangan PGA untuk verifikasi!");
                e.preventDefault(); return false;
            }
            else if (isUserEmpty) {
                alert("Tanda tangan USER tidak boleh dihapus/kosong!");
                e.preventDefault(); return false;
            }
        } 
        
        else if (role === 'user') {
            if (isUserEmpty) {
                alert("Anda wajib tanda tangan USER sebagai konfirmasi!");
                e.preventDefault(); return false;
            }

            const ttdDbUser = `<?php echo $d['ttd_user']; ?>`;
            const waChecked = document.getElementById('wa_teknisi').value;
            
            if (ttdDbUser === "" && waChecked === "") {
                e.preventDefault();
                document.getElementById('modalTeknisi').style.display = 'flex';
                return false;
            }
        }
    };
</script>
</body>
</html>