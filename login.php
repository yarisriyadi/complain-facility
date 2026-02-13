<?php 
session_start(); 
include 'config_maintenance.php'; 

if (isset($_SESSION['status']) && $_SESSION['status'] == "login") {
    if ($_SESSION['role'] == 'admin') {
        header("location:admin_dashboard_proses.php");
    } else {
        if ($maintenance_mode) {
            header("location:maintenance.php");
        } else {
            header("location:index.php");
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
    <title>Login - SHINSEI</title>
    
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
            overflow: hidden; 
        }
        .login-container { 
            background: var(--container-bg); 
            backdrop-filter: blur(10px); 
            -webkit-backdrop-filter: blur(10px); 
            padding: 35px; border-radius: 16px; 
            border: 1px solid var(--border-color); 
            box-shadow: 0 8px 32px var(--shadow); 
            width: 90%; max-width: 380px; 
            box-sizing: border-box; position: relative; z-index: 1; 
            transition: all 0.4s ease;
        }
        .login-container h2, .login-container label, .footer-links, .copyright { 
            color: var(--text-color); 
            transition: color 0.4s ease;
        }
        .login-container h2 { 
            text-align: center; 
            margin: 0 0 25px 0; 
            font-size: 26px; 
            font-weight: bold; 
            letter-spacing: 2px; 
            text-transform: uppercase; 
        }
        .form-group { 
            margin-bottom: 20px; 
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
            border-radius: 8px; 
            box-sizing: border-box; 
            font-size: 15px; 
            background: var(--input-bg); 
            color: var(--text-color); 
            transition: 0.3s; 
        }
        .btn-login { 
            width: 100%; 
            padding: 14px; 
            background-color: #007bff; 
            border: none; 
            color: white; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold; 
            margin-top: 10px; 
            text-transform: uppercase; 
            transition: all 0.4s ease; 
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }
        .btn-login:hover:not(:disabled) { 
            background-color: #0056b3; 
            transform: translateY(-2px); 
        }
        .btn-login:disabled {
            background-color: #555555 !important; 
            color: #cccccc; cursor: not-allowed;
        }
        .alert { 
            padding: 12px; 
            border-radius: 8px; 
            font-size: 13px; 
            text-align: center; 
            margin-bottom: 20px; 
            line-height: 1.5; 
    }
        .error { 
            background-color: #fce4e4; 
            color: #963b3b; 
            border: 1px solid #f9cccc; 
    }
        .success { 
            background-color: #d4edda; 
            color: #155724;
            border: 1px solid #c3e6cb; 
    }
        .warning-session { 
            background-color: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeeba; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 8px; 
    }    
        .footer-links { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 14px; 
    }
        .reg-link { 
            color: #2ecc71 !important; 
            text-decoration: none; 
            font-weight: bold; 
            transition: 0.3s; 
    }
        .reg-link:hover { 
            text-decoration: underline; 
    }
        .copyright { 
            text-align: center; 
            margin-top: 30px; 
            font-size: 10px; 
            border-top: 1px solid var(--border-color); 
            padding-top: 15px; 
    }
        .password-container { 
        position: relative; 
        width: 100%; 
    }
        .toggle-password { 
        position: absolute; 
        right: 15px; 
        top: 50%; 
        transform: translateY(-50%); 
        cursor: pointer; 
        color: #666; 
        font-size: 18px; 
        z-index: 10;
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
        <h2>LOGIN</h2>
        
        <?php 
        $is_locked = (isset($_SESSION['attempt']) && $_SESSION['attempt'] >= 5);
        
        if ($is_locked) {
            echo "<div class='alert error'>
                    <strong>Sistem Terkunci!</strong><br>
                    Batas percobaan login tercapai.<br>
                    <a href='lupa_password.php' style='color:#d93025; font-weight:bold;'>Klik di sini untuk Reset Password</a>
                  </div>";
        } else {
            if (isset($_SESSION['error_msg'])) {
                echo "<div class='alert error'>" . $_SESSION['error_msg'] . "</div>";
                unset($_SESSION['error_msg']); 
            }

            if (isset($_GET['pesan'])) {
                $pesan = htmlspecialchars($_GET['pesan']);
                if ($pesan == "sesi_habis") { 
                    echo "<div class='alert warning-session'>
                            <span>Sesi berakhir. Silakan login kembali.</span>
                          </div>"; 
                }
                else if ($pesan == "logout") { 
                    echo "<div class='alert success'>Berhasil logout.</div>"; 
                }
                else if ($pesan == "belum_login") { 
                    echo "<div class='alert error'>Silakan login terlebih dahulu.</div>"; 
                }
                else if ($pesan == "berhasil_regis") { 
                    echo "<div class='alert success'>Registrasi Berhasil! Silakan Login.</div>"; 
                }
            }
        }
        ?>

        <form action="proses_login.php" method="POST">
            <div class="form-group">
                <label>Username / Email</label> 
                <input type="text" name="username" required placeholder="Username atau Email" autocomplete="off" <?php echo $is_locked ? 'disabled' : ''; ?>>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="password-container">
                    <input type="password" name="password" id="password" required placeholder="Password" <?php echo $is_locked ? 'disabled' : ''; ?>>
                    <i class="fa-solid fa-eye-slash toggle-password" id="togglePassword"></i>
                </div>
            </div>
            <button type="submit" class="btn-login" <?php echo $is_locked ? 'disabled' : ''; ?>>LOGIN</button>
        </form>

        <div class="footer-links">
            Belum punya akun? <a href="register.php" class="reg-link">Registrasi</a>
        </div>
        
        <div class="copyright">&copy; 2026 PT. Shinsei Denshi Indonesia.</div>
    </div>

    <script src="theme_script.js"></script>
    <script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordField = document.querySelector('#password');
    
    togglePassword.addEventListener('click', function () {
        if (!passwordField.disabled) {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        }
    });

    if (window.history.replaceState) {
        const url = new URL(window.location);
        if (url.searchParams.has('pesan')) { 
            url.searchParams.delete('pesan');
            window.history.replaceState({}, document.title, url.pathname);
        }
    }
</script>
</body>
</html>