<?php
session_start();
include 'config_maintenance.php';
cek_akses_maintenance($maintenance_mode);
include 'koneksi.php';

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: lupa_password.php");
    exit();
}
date_default_timezone_set('Asia/Jakarta');

$update_success = false;
$error_message = "";

if (isset($_POST['update_password'])) {
    $email = $_SESSION['email_reset'];
    $pw_baru = $_POST['new_password'];
    $confirm_pw = $_POST['confirm_password'];

    $hasSymbol = preg_match('/[!@#$%^&*(),.?":{}|<>_]/', $pw_baru);
    $hasNumber = preg_match('/[0-9]/', $pw_baru);
    $hasUpper = preg_match('/^[A-Z]/', $pw_baru);

    if ($pw_baru !== $confirm_pw) {
        $error_message = "Konfirmasi password tidak cocok!";
    } 
    elseif (strlen($pw_baru) < 6 || !$hasUpper || !$hasNumber || !$hasSymbol) {
        $error_message = "Password tidak memenuhi kriteria keamanan!";
    } 
    else {
        $password_hashed = password_hash($pw_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET password = '$password_hashed', otp_code = NULL, otp_expiry = NULL WHERE email = '$email'");

        if ($update) {
            $email_user = $_SESSION['email_reset'];
            $ambil_user = mysqli_query($conn, "SELECT nama_lengkap FROM users WHERE email = '$email_user'");
            $data_user = mysqli_fetch_assoc($ambil_user);
            $nama_lengkap = $data_user['nama_lengkap']; 
        if (!$nama_lengkap) { $nama_lengkap = $email_user; }
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'pthtmi123@gmail.com'; 
                $mail->Password   = 'xuvalxykuepbpblc'; // App Password Gmail
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('pthtmi123@gmail.com', 'SHINSEI SYSTEM');
                $mail->addAddress($email); 

                $mail->isHTML(true);
                $mail->Subject = 'Notifikasi Keamanan: Perubahan Password Berhasil';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; border: 1px solid #eee; padding: 20px;'>
                        <h2 style='color: #28a745;'>Password Berhasil Diperbarui</h2>
                        <p>Halo, <b>" . $nama_lengkap . "</b></p>
                        <p>Kami memberitahukan bahwa password akun Anda telah berhasil diubah pada <b>" . date('d-m-Y H:i:s') . "</b>.</p>
                        <p>Jika Anda tidak merasa melakukan perubahan ini, segera hubungi tim kami..</p>
                        <br>
                        <p>Terima Kasih,<br>IT Department - PT. Shinsei Denshi Indonesia.</p>
                    </div>";

                $mail->send();
            } catch (Exception $e) {
        }
            $update_success = true;
        } else {
            $error_message = "Gagal memperbarui database!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reset Password - SHINSEI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('selected-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <link rel="stylesheet" href="style_theme.css">
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 0; padding: 0; 
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh;
            background-color: var(--bg-color);
            transition: background-color 0.4s ease;
        }

        .login-container { 
            background: var(--container-bg); 
            backdrop-filter: blur(10px); 
            padding: 35px; 
            border-radius: 16px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px var(--shadow); 
            width: 90%; max-width: 400px; 
            box-sizing: border-box;
            transition: all 0.4s ease;
            animation: fadeInUp 0.6s ease-out; /* Trigger animasi */
        }

        .login-container h2 { 
            text-align: center; margin: 0 0 10px 0; font-size: 24px; 
            font-weight: bold; text-transform: uppercase; letter-spacing: 2px;
            color: var(--text-color);
        }

        .login-container p { 
            text-align: center; font-size: 13px; margin-bottom: 25px; 
            opacity: 0.8; line-height: 1.5; color: var(--text-color);
        }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 600; color: var(--text-color); }
        
        .password-container { position: relative; width: 100%; }
        
        .form-group input { 
            width: 100%; padding: 12px; border: 1px solid var(--border-color); 
            border-radius: 8px; box-sizing: border-box; font-size: 15px; 
            background: var(--input-bg); color: var(--text-color); 
            transition: all 0.3s ease; 
        }

        .form-group input:focus { 
            border-color: #28a745; outline: none; 
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.3); 
            transform: scale(1.01); 
        }

        .toggle-password { 
            position: absolute; right: 15px; top: 50%; 
            transform: translateY(-50%); cursor: pointer; 
            color: var(--text-color); opacity: 0.6; 
        }

        .btn-submit { 
            width: 100%; padding: 14px; background-color: #28a745; 
            border: none; color: white; border-radius: 8px; 
            cursor: pointer; font-size: 16px; font-weight: bold; 
            margin-top: 10px; text-transform: uppercase;
            transition: 0.3s; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-submit:hover:not(:disabled) { 
            background-color: #218838; transform: translateY(-2px); 
        }

        .btn-submit:active { transform: translateY(0); }

        .btn-submit:disabled { opacity: 0.5; filter: grayscale(1); cursor: not-allowed; }

        .copyright { 
            text-align: center; margin-top: 30px; font-size: 10px; 
            border-top: 1px solid var(--border-color); padding-top: 15px; 
            opacity: 0.6; color: var(--text-color);
        }

        .theme-switcher { position: fixed; bottom: 25px; left: 25px; z-index: 1000; }
        
        .msg-error { font-size: 11px; margin-top: 5px; font-weight: bold; color: #ff4d4d; }
        .msg-success { font-size: 11px; margin-top: 5px; font-weight: bold; color: #2ecc71; }
    </style>
</head>
<body>

    <div class="theme-switcher">
        <button class="theme-btn" onclick="toggleTheme()" title="Ganti Tema">
            <i id="theme-icon-sun" class="fa-solid fa-sun" style="color: #f1c40f;"></i>
            <i id="theme-icon-moon" class="fa-solid fa-moon" style="color: #f1c40f;"></i>
        </button>
    </div>

    <div class="login-container">
        <h2>PASSWORD BARU</h2>
        <p>Silakan buat password baru Anda.<br>Gunakan kombinasi Huruf Besar, Angka, dan Simbol.</p>

        <form action="" method="POST" id="resetForm">
            <div class="form-group">
                <label>Password Baru</label>
                <div class="password-container">
                    <input type="password" name="new_password" id="new_password" required placeholder="Awal Besar, Min 6 Karakter" autofocus>
                    <i class="fa-solid fa-eye-slash toggle-password" onclick="toggle('new_password', this)"></i>
                </div>
                <div id="pass-message"></div>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <div class="password-container">
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Ulangi password">
                    <i class="fa-solid fa-eye-slash toggle-password" onclick="toggle('confirm_password', this)"></i>
                </div>
                <div id="confirm-message"></div>
            </div>

            <button type="submit" name="update_password" class="btn-submit" id="submitBtn" disabled>UPDATE PASSWORD</button>
        </form>

        <div class="copyright">&copy; 2026 PT. Shinsei Denshi Indonesia.</div>
    </div>

    <script src="theme_script.js"></script>
    <script>
        <?php if ($update_success): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Password berhasil diperbarui.',
            showConfirmButton: false,
            timer: 2500,
            background: getComputedStyle(document.documentElement).getPropertyValue('--container-bg'),
            color: getComputedStyle(document.documentElement).getPropertyValue('--text-color')
        }).then(() => {
            window.location = 'login.php?pesan=update_berhasil'; 
        });
    <?php elseif ($error_message !== ""): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo $error_message; ?>',
                confirmButtonColor: '#28a745'
            });
        <?php endif; ?>

        const resetForm = document.getElementById('resetForm');
        resetForm.addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...';
            btn.style.opacity = '0.7';
        });

        function toggle(id, el) {
            const x = document.getElementById(id);
            if (x.type === "password") {
                x.type = "text";
                el.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                x.type = "password";
                el.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }

        const passwordInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('confirm_password');
        const passMsg = document.getElementById('pass-message');
        const confirmMsg = document.getElementById('confirm-message');
        const submitBtn = document.getElementById('submitBtn');

        function validateForm() {
            const pass = passwordInput.value;
            const confirm = confirmInput.value;
            
            const hasSymbol = /[!@#$%^&*(),.?":{}|<>_]/.test(pass);
            const hasNumber = /[0-9]/.test(pass);
            const hasUpper = /^[A-Z]/.test(pass);

            let isPassValid = false;
            let isConfirmValid = false;

            if (pass !== "") {
                if (pass.length >= 6 && hasUpper && hasNumber && hasSymbol) {
                    passMsg.className = "msg-success";
                    passMsg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Password Kuat';
                    isPassValid = true;
                } else {
                    passMsg.className = "msg-error";
                    passMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Belum sesuai kriteria';
                }
            } else { passMsg.innerHTML = ""; }

            if (confirm !== "") {
                if (confirm === pass && pass !== "") {
                    confirmMsg.className = "msg-success";
                    confirmMsg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Password Cocok';
                    isConfirmValid = true;
                } else {
                    confirmMsg.className = "msg-error";
                    confirmMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Tidak cocok!';
                }
            } else { confirmMsg.innerHTML = ""; }

            submitBtn.disabled = !(isPassValid && isConfirmValid);
        }

        passwordInput.addEventListener('input', validateForm);
        confirmInput.addEventListener('input', validateForm);
    </script>
</body>
</html>