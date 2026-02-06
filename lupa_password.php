<?php 
session_start(); 

if (file_exists('config_maintenance.php')) {
    require_once 'config_maintenance.php';
}

if (isset($maintenance_mode) && $maintenance_mode === true) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: maintenance.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SHINSEI</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style_theme.css">
    
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 0; padding: 0; 
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh; overflow: hidden;
        }

        /* Container menggunakan variabel dari style_theme.css */
        .login-container { 
            background: var(--container-bg); 
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); 
            padding: 35px; border-radius: 16px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px var(--shadow); 
            width: 90%; max-width: 380px; 
            box-sizing: border-box; position: relative; z-index: 1;
            transition: all 0.4s ease;
        }

        .login-container h2, .login-container p, .login-container label, .copyright {
            color: var(--text-color);
            transition: color 0.4s ease;
        }

        .login-container h2 { 
            text-align: center; margin: 0 0 10px 0; font-size: 24px; 
            font-weight: bold; text-transform: uppercase; letter-spacing: 2px;
        }

        .login-container p { text-align: center; font-size: 14px; margin-bottom: 5px; line-height: 1.5; opacity: 0.9; }

        .lupa-email-wrapper { text-align: center; margin-bottom: 20px; }
        
        .lupa-email-link { 
            font-size: 12px; 
            color: #28a745; 
            text-decoration: none; 
            font-weight: bold; 
            transition: 0.3s; 
        }
        .lupa-email-link:hover { text-decoration: underline; color: #218838; }

        .form-group { margin-bottom: 20px; position: relative; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: bold; }

        .form-group input { 
            width: 100%; padding: 12px; border: 1px solid var(--border-color); 
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
            width: 100%; padding: 14px; background-color: #28a745; border: none; 
            color: white; border-radius: 8px; cursor: pointer; font-size: 16px; 
            font-weight: bold; margin-bottom: 12px; text-transform: uppercase;
            transition: all 0.4s ease; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-submit:hover:not(:disabled) { background-color: #218838; transform: translateY(-2px); }
        
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

        .copyright { text-align: center; margin-top: 30px; font-size: 10px; border-top: 1px solid var(--border-color); padding-top: 15px; }
        
        .alert-maintenance {
            background: #fff3cd; color: #856404; padding: 10px; border-radius: 8px;
            margin-bottom: 15px; text-align: center; font-size: 13px; border: 1px solid #ffeeba;
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
        <h2>LUPA PASSWORD</h2>

        <?php if (isset($maintenance_mode) && $maintenance_mode === true): ?>
            <div class="alert-maintenance">
                <i class="fas fa-exclamation-triangle"></i> <strong>Mode Maintenance Aktif</strong>
            </div>
        <?php endif; ?>

        <p>Masukkan alamat email Anda untuk menerima kode verifikasi OTP.</p>
        
        <div class="lupa-email-wrapper">
            <a href="lupa_email.php" class="lupa-email-link">Lupa alamat email?</a>
        </div>
        
        <form action="proses_kirim_otp.php" method="POST" id="resetForm">
            <div class="form-group">
                <label>Alamat Email</label>
                <input type="email" name="email" id="emailInput" required placeholder="user@gmail.com" autocomplete="off">
                <div id="emailHint" class="email-warning"></div>
            </div>
            
            <button type="submit" name="submit_lupa" id="submitBtn" class="btn-submit">
                KIRIM KODE OTP
            </button>
            
            <a href="login.php" class="btn-back">
                KEMBALI KE LOGIN
            </a>
        </form>

        <div class="copyright">&copy; 2026 PT. Shinsei Denshi Indonesia.</div>
    </div>

    <script src="theme_script.js"></script>

    <script>
        const emailInput = document.getElementById('emailInput');
        const emailHint = document.getElementById('emailHint');
        const submitBtn = document.getElementById('submitBtn');

        emailInput.addEventListener('input', function() {
            const emailOriginal = this.value.trim();
            const emailValue = emailOriginal.toLowerCase();
            
            let errorMessage = "";
            let isError = false;

            if (emailOriginal.length > 0) {
                if (/[A-Z]/.test(emailOriginal)) {
                    errorMessage = "<i class='fas fa-exclamation-circle'></i> Gunakan huruf kecil semua.";
                    isError = true;
                } 
                else if (!emailValue.endsWith('@gmail.com')) {
                    errorMessage = "<i class='fas fa-exclamation-circle'></i> Wajib menggunakan @gmail.com";
                    isError = true;
                }

                if (isError) {
                    emailHint.innerHTML = errorMessage;
                    emailHint.style.display = 'block';
                    submitBtn.disabled = true;
                } else {
                    emailHint.style.display = 'none';
                    submitBtn.disabled = false;
                }
            } else {
                emailHint.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
    </script>

</body>
</html>