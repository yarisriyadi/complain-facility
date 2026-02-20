<?php 
session_start(); 
include 'config_maintenance.php';
cek_akses_maintenance($maintenance_mode);

include 'koneksi.php';

if (isset($_POST['update_email'])) {
    $email_lama = mysqli_real_escape_string($conn, $_POST['email_lama']);
    $email_baru = mysqli_real_escape_string($conn, $_POST['email_baru']);

    $cek_email_lama = mysqli_query($conn, "SELECT * FROM users WHERE email='$email_lama'");
    
    if (mysqli_num_rows($cek_email_lama) > 0) {
        
        $cek_email_baru = mysqli_query($conn, "SELECT * FROM users WHERE email='$email_baru'");
        
        if (mysqli_num_rows($cek_email_baru) > 0) {
            $error = "Email baru sudah terdaftar! Gunakan email lain.";
        } else {
            $update = mysqli_query($conn, "UPDATE users SET email='$email_baru' WHERE email='$email_lama'");
            
            if ($update) {
                $success = "Email berhasil diperbarui! Silakan gunakan email baru.";
            } else {
                $error = "Gagal memperbarui database. Silakan hubungi admin.";
            }
        }
    } else {
        $error = "Email lama tidak terdaftar dalam sistem!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPDATE EMAIL - CF</title>
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
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            overflow: hidden;
            background-color: var(--bg-color);
        }
        .login-container { 
            background: var(--container-bg); 
            backdrop-filter: blur(10px); 
            -webkit-backdrop-filter: blur(10px); 
            padding: 35px; 
            border-radius: 16px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px var(--shadow); 
            width: 90%; 
            max-width: 380px; 
            box-sizing: border-box; 
            position: relative; 
            z-index: 1;
            transition: all 0.4s ease;
        }
        .login-container h2, .desc, .login-container label, .copyright {
            color: var(--text-color);
            transition: color 0.4s ease;
        }
        .login-container h2 { 
            text-align: center; 
            margin: 0 0 10px 0; 
            font-size: 24px; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 2px;
        }
        .desc { 
            text-align: center; 
            font-size: 14px; 
            margin-bottom: 20px; 
            line-height: 1.5; 
            opacity: 0.8; 
    }
        .form-group { 
            margin-bottom: 20px; 
            position: relative; 
    }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-size: 14px; 
            font-weight: bold; 
    }
        .form-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid var(--border-color); 
            border-radius: 8px; box-sizing: border-box; font-size: 15px; 
            background: var(--input-bg); color: #333; transition: 0.3s;
    }
        .email-warning {
            color: #ff4d4d;
            font-size: 11px;
            margin-top: 5px;
            display: none; 
            font-weight: bold;
            line-height: 1.4;
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
            margin-bottom: 12px; 
            text-transform: uppercase;
            transition: all 0.4s ease; 
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }
        .btn-submit:hover:not(:disabled) { 
        background-color: #218838; 
        transform: translateY(-2px); 
    }    
        .btn-submit:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            opacity: 0.7;
    }
        .btn-back {
            width: 100%; 
            padding: 12px; 
            background-color: transparent;
            border: 1px solid var(--border-color); 
            color: var(--text-color); 
            border-radius: 8px; 
            text-decoration: none; 
            display: inline-block;
            text-align: center; 
            box-sizing: border-box; 
            transition: 0.3s;
            font-weight: bold; 
            font-size: 14px;
    }
        .btn-back:hover { 
            background-color: var(--container-bg);
            border-color: var(--text-color); 
    }
        .alert { 
        padding: 12px; 
        border-radius: 8px; 
        font-size: 13px; 
        text-align: center; 
        margin-bottom: 15px; 
    }
        .alert-success { 
        background: #d4edda; 
        color: #155724; 
        border: 1px solid #c3e6cb; 
    }
        .alert-error { 
        background: #f8d7da; 
        color: #721c24; 
        border: 1px solid #f5c6cb; 
    }
        .copyright { 
        text-align: center; 
        margin-top: 30px; 
        font-size: 10px; 
        border-top: 1px solid var(--border-color); 
        padding-top: 15px; 
    }
    .theme-switcher {
            position: fixed;
            bottom: 25px;
            left: 25px;
            z-index: 1000;
        }
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
        <h2>PERBARUI EMAIL</h2>
        <div class="desc">Ganti email lama dengan email baru</div>

        <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if(isset($error)) echo "<div class='alert alert-error'>$error</div>"; ?>

        <form method="POST" id="emailForm">
            <div class="form-group">
                <label>Email Lama</label>
                <input type="email" name="email_lama" id="emailLama" required placeholder="user@gmail.com" autocomplete="off">
                <div id="hintLama" class="email-warning"></div>
            </div>

            <div class="form-group">
                <label>Email Baru</label>
                <input type="email" name="email_baru" id="emailBaru" required placeholder="baru@gmail.com" autocomplete="off">
                <div id="hintBaru" class="email-warning"></div>
            </div>
            
            <button type="submit" name="update_email" id="submitBtn" class="btn-submit">
                UPDATE EMAIL
            </button>
            
            <a href="lupa_password.php" class="btn-back">
                KEMBALI
            </a>
        </form>

        <div class="copyright">&copy; 2026 PT. Shinsei Denshi Indonesia.</div>
    </div>

    <script src="theme_script.js"></script>

    <script>
        const emailLama = document.getElementById('emailLama');
        const emailBaru = document.getElementById('emailBaru');
        const hintLama = document.getElementById('hintLama');
        const hintBaru = document.getElementById('hintBaru');
        const submitBtn = document.getElementById('submitBtn');

        function validateEmail(input, hint) {
            const originalValue = input.value.trim();
            const lowerValue = originalValue.toLowerCase();
            let isError = false;
            let message = "";

            if (originalValue.length > 0) {
                if (/[A-Z]/.test(originalValue)) {
                    message = "<i class='fas fa-exclamation-circle'></i> Gunakan huruf kecil semua.";
                    isError = true;
                } else if (!lowerValue.endsWith('@gmail.com')) {
                    message = "<i class='fas fa-exclamation-circle'></i> Wajib menggunakan @gmail.com";
                    isError = true;
                }
            }

            if (isError) {
                hint.innerHTML = message;
                hint.style.display = 'block';
            } else {
                hint.style.display = 'none';
            }
            return isError;
        }

        function checkAll() {
            const errorLama = validateEmail(emailLama, hintLama);
            const errorBaru = validateEmail(emailBaru, hintBaru);
            submitBtn.disabled = (errorLama || errorBaru);
        }

        emailLama.addEventListener('input', checkAll);
        emailBaru.addEventListener('input', checkAll);
    </script>

</body>
</html>