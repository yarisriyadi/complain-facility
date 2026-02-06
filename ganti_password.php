<?php
session_start();
include 'config_maintenance.php';
cek_akses_maintenance($maintenance_mode);
include 'koneksi.php';

if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: lupa_password.php");
    exit();
}

if (isset($_POST['update_password'])) {
    $email = $_SESSION['email_reset'];
    $pw_baru = $_POST['new_password'];
    $confirm_pw = $_POST['confirm_password'];

    $hasSymbol = preg_match('/[!@#$%^&*(),.?":{}|<>_]/', $pw_baru);
    $hasNumber = preg_match('/[0-9]/', $pw_baru);
    $hasUpper = preg_match('/^[A-Z]/', $pw_baru);

    if ($pw_baru !== $confirm_pw) {
        $error = "Konfirmasi password tidak cocok!";
    } 
    elseif (strlen($pw_baru) < 6 || !$hasUpper || !$hasNumber || !$hasSymbol) {
        $error = "Password tidak memenuhi kriteria keamanan!";
    } 
    else {
        $password_hashed = password_hash($pw_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET password = '$password_hashed', otp_code = NULL, otp_expiry = NULL WHERE email = '$email'");

        if ($update) {
            session_destroy();
            echo "<script>alert('Password berhasil diperbarui! Silakan login kembali.'); window.location='login.php';</script>";
            exit();
        } else {
            $error = "Gagal memperbarui database!";
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
    <link rel="stylesheet" href="style_theme.css">
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh;
            background-color: var(--bg-color);
            transition: background-color 0.4s ease;
        }

        .login-container { 
            background: var(--container-bg); 
            backdrop-filter: blur(10px); 
            -webkit-backdrop-filter: blur(10px);
            padding: 35px; 
            border-radius: 16px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px var(--shadow); 
            width: 90%; max-width: 400px; 
            box-sizing: border-box;
            transition: all 0.4s ease;
        }

        .login-container h2, .login-container p, .login-container label, .copyright {
            color: var(--text-color);
        }

        .login-container h2 { 
            text-align: center; 
            margin: 0 0 10px 0; 
            font-size: 24px; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 2px;
        }

        .login-container p { 
        text-align: center; 
        font-size: 13px; 
        margin-bottom: 25px; 
        opacity: 0.8; 
        line-height: 1.5; 
    }

        .form-group { 
        margin-bottom: 20px; 
    }
        .form-group label { 
        display: block; 
        margin-bottom: 8px; 
        font-size: 14px; 
        font-weight: 600; 
    }
        .password-container { 
        position: relative; 
        width: 100%; 
    }

        .form-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid var(--border-color); 
            border-radius: 8px; 
            box-sizing: border-box; 
            font-size: 15px; 
            background: var(--input-bg); 
            color: var(--text-color); 
            transition: all 0.3s ease; 
        }

        [data-theme="light"] .form-group input { 
        color: #333; 
    }
        [data-theme="dark"] .form-group input { 
        color: #fff; 
        background: rgba(255,255,255,0.05); 
    }

        .form-group input:focus { 
        border-color: #28a745; 
        outline: none; 
        box-shadow: 0 0 10px rgba(40, 167, 69, 0.3); 
        }

        .toggle-password { 
            position: absolute; 
            right: 15px; 
            top: 50%; 
            transform: translateY(-50%); 
            cursor: pointer; 
            color: var(--text-color); 
            opacity: 0.6; 
            font-size: 16px; 
            z-index: 10; 
        }

        .msg-error { 
        font-size: 11px; 
        margin-top: 5px; 
        font-weight: bold; 
        color: #ff4d4d; 
    }
        .msg-success { 
        font-size: 11px; 
        margin-top: 5px; 
        font-weight: bold; 
        color: #2ecc71; 
    }

        .btn-submit { 
            width: 100%; 
            padding: 14px; 
            background-color: #28a745; 
            border: none; 
            color: white; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold; 
            margin-top: 10px; 
            text-transform: uppercase;
            transition: 0.4s; 
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-submit:hover:not(:disabled) { 
        background-color: #218838; 
        transform: translateY(-2px); 
    }
        .btn-submit:disabled { 
        opacity: 0.5; cursor: not-allowed; 
        filter: grayscale(1); 
    }

        .alert { 
            padding: 12px; 
            border-radius: 8px; 
            font-size: 13px; 
            text-align: center; 
            margin-bottom: 20px; 
            background: rgba(255, 82, 82, 0.15); 
            color: #ff4d4d; 
            border: 1px solid #ff4d4d; 
        }

        .copyright { 
            text-align: center; 
            margin-top: 30px; 
            font-size: 10px; 
            border-top: 1px solid var(--border-color); 
            padding-top: 15px; 
            opacity: 0.6;
        }
    </style>
</head>
<body>

    <script>
        const savedTheme = localStorage.getItem('selected-theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
    </script>

    <div class="theme-switcher">
        <button class="theme-btn" onclick="toggleTheme()" title="Ganti Tema">
            <i id="theme-icon-sun" class="fa-solid fa-sun" style="color: #f1c40f;"></i>
            <i id="theme-icon-moon" class="fa-solid fa-moon" style="color: #f1c40f;"></i>
        </button>
    </div>

    <div class="login-container">
        <h2>PASSWORD BARU</h2>
        <p>Silakan buat password baru Anda.<br>Gunakan kombinasi Huruf Besar, Angka, dan Simbol.</p>

        <?php if(isset($error)) echo "<div class='alert'>$error</div>"; ?>

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

            if (pass === "") {
                passMsg.innerHTML = "";
            } else if (!hasUpper) {
                passMsg.className = "msg-error";
                passMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Diawali Huruf Besar!';
            } else if (pass.length < 6) {
                passMsg.className = "msg-error";
                passMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Minimal 6 karakter!';
            } else if (!hasNumber) {
                passMsg.className = "msg-error";
                passMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Wajib ada Angka!';
            } else if (!hasSymbol) {
                passMsg.className = "msg-error";
                passMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Wajib ada Simbol!';
            } else {
                passMsg.className = "msg-success";
                passMsg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Password Kuat';
                isPassValid = true;
            }

            if (confirm === "") {
                confirmMsg.innerHTML = "";
            } else if (confirm !== pass) {
                confirmMsg.className = "msg-error";
                confirmMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Password tidak cocok!';
            } else {
                confirmMsg.className = "msg-success";
                confirmMsg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Password Cocok';
                isConfirmValid = true;
            }

            submitBtn.disabled = !(isPassValid && isConfirmValid);
        }

        passwordInput.addEventListener('input', validateForm);
        confirmInput.addEventListener('input', validateForm);
    </script>
</body>
</html>