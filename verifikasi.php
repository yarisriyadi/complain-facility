<?php
session_start();
require_once 'config_maintenance.php';
cek_akses_maintenance($maintenance_mode);
include 'koneksi.php';

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['email_reset'])) {
    header("Location: lupa_password.php");
    exit();
}

$email = $_SESSION['email_reset'];

$query_otp = mysqli_query($conn, "SELECT otp_expiry, otp_code FROM users WHERE email='$email'");
$data_otp = mysqli_fetch_assoc($query_otp);

if (!$data_otp) {
    session_destroy();
    echo "<script>alert('Sesi tidak valid!'); window.location='lupa_password.php';</script>";
    exit();
}

$expiry_db = $data_otp['otp_expiry'];
$otp_db    = $data_otp['otp_code'];

if (isset($_POST['verifikasi'])) {
    $otp_input = mysqli_real_escape_string($conn, $_POST['otp_code']);
    $waktu_sekarang = date("Y-m-d H:i:s");

    if ($otp_input === $otp_db) {
        if ($waktu_sekarang <= $expiry_db) {
            $_SESSION['otp_verified'] = true;
            header("Location: ganti_password.php");
            exit();
        } else {
            $error = "Kode OTP sudah kedaluwarsa!";
        }
    } else {
        $error = "Kode OTP salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Verifikasi OTP - SHINSEI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style_theme.css">
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 0; padding: 0; 
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh; overflow: hidden;
            background-color: var(--bg-color);
        }

        .login-container { 
            background: var(--container-bg); 
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            padding: 35px; border-radius: 16px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px var(--shadow); 
            width: 90%; max-width: 420px; 
            box-sizing: border-box; position: relative; z-index: 1;
            transition: all 0.4s ease;
        }

        .login-container h2, .login-container p, .copyright, .timer-container {
            color: var(--text-color);
        }

        .login-container h2 { 
            text-align: center; margin: 0 0 10px 0; font-size: 24px; 
            font-weight: bold; text-transform: uppercase; letter-spacing: 2px;
        }

        .login-container p { text-align: center; font-size: 14px; margin-bottom: 20px; line-height: 1.5; }

        .email-display { color: #2ecc71; font-weight: 700; display: block; font-size: 15px; word-break: break-all; }

        .timer-container { 
            text-align: center; margin-bottom: 20px; padding: 12px; 
            background: rgba(128, 128, 128, 0.1); 
            border: 1px solid var(--border-color); 
            border-radius: 8px; font-size: 14px; font-weight: 600; 
        }

        #countdown { font-size: 18px; display: block; color: #2ecc71; }

        .otp-wrapper { 
            display: flex; justify-content: space-between; 
            gap: 8px; margin-bottom: 25px; direction: ltr;
        }

        .otp-box { 
            width: 100%; height: 55px; text-align: center; 
            font-size: 24px; font-weight: bold; 
            border: 1px solid var(--border-color); 
            border-radius: 10px; background: var(--input-bg); 
            color: #333; transition: all 0.2s ease; 
        }

        .otp-box:focus { 
            border-color: #28a745; 
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.4); 
            outline: none; transform: scale(1.05);
        }

        .btn-submit { 
            width: 100%; padding: 14px; background-color: #28a745; border: none; 
            color: white; border-radius: 8px; cursor: pointer; font-size: 16px; 
            font-weight: bold; text-transform: uppercase; transition: 0.4s; 
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-submit:hover:not(:disabled) { background-color: #218838; transform: translateY(-2px); }
        .btn-submit:disabled { background-color: #666; cursor: not-allowed; opacity: 0.6; }

        .alert { 
            background: rgba(255, 82, 82, 0.2); color: #ff4d4d; 
            padding: 10px; border-radius: 6px; font-size: 12px; 
            margin-bottom: 15px; border: 1px solid #ff4d4d; text-align: center; 
        }

        .footer-links { text-align: center; margin-top: 20px; font-size: 13px; color: var(--text-color); }
        .footer-links a, .resend-btn { color: #2ecc71; text-decoration: none; font-weight: bold; background: none; border: none; cursor: pointer; font-size: 13px; }
        .copyright { text-align: center; margin-top: 25px; font-size: 10px; border-top: 1px solid var(--border-color); padding-top: 15px; }

        @media screen and (max-width: 380px) { .otp-box { height: 45px; font-size: 20px; } }
    </style>
</head>
<body>

    <script>
        // Sinkronisasi Tema Sebelum Render
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
        <h2>Verifikasi OTP</h2>
        <p>Kode telah dikirim ke email:<br><span class="email-display"><?php echo htmlspecialchars($email); ?></span></p>

        <div class="timer-container" id="timer-box">
            Sisa Waktu: <span id="countdown">--:--</span>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST" id="otp-form">
            <input type="text" id="otp_autofill" 
                   style="position: absolute; opacity: 0; pointer-events: none;" 
                   autocomplete="one-time-code" inputmode="numeric">

            <div class="otp-wrapper">
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="\d*" required autofocus>
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="\d*" required>
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="\d*" required>
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="\d*" required>
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="\d*" required>
                <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="\d*" required>
            </div>
            
            <input type="hidden" name="otp_code" id="otp_hidden">
            <input type="hidden" name="verifikasi" value="1">
            <button type="submit" name="verifikasi" id="btn-submit" class="btn-submit">VERIFIKASI</button>
        </form>

        <div class="footer-links">
            <div id="resend-wrapper" style="display:none;">
                Tidak menerima email? 
                <form action="proses_kirim_otp.php" method="POST" style="display:inline;">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <button type="submit" name="submit_lupa" class="resend-btn">Kirim Ulang</button>
                </form>
            </div>
            <a href="lupa_password.php" id="change-num-link">Ganti Alamat Email?</a>
        </div>
        <div class="copyright">&copy; 2026 PT. Shinsei Denshi Indonesia.</div>
    </div>

    <script src="theme_script.js"></script>

    <script>
    const boxes = document.querySelectorAll('.otp-box');
    const hiddenInput = document.getElementById('otp_hidden');
    const autofillInput = document.getElementById('otp_autofill');
    const otpForm = document.getElementById('otp-form');

    // --- 1. FUNGSI GABUNG & AUTO SUBMIT ---
    function combineOTP() {
        let code = "";
        boxes.forEach(box => code += box.value);
        hiddenInput.value = code;
        
        // Jika sudah 6 digit, otomatis submit
        if (code.length === 6) {
            otpForm.submit();
        }
    }

    // --- 2. LOGIKA AUTO COPY-PASTE (UTAMA) ---
    boxes.forEach((box, index) => {
        box.addEventListener('paste', (e) => {
            e.preventDefault(); 
            const data = (e.clipboardData || window.clipboardData).getData('text').trim();
            
            if (/^\d+$/.test(data)) {
                const digits = data.split('');
                digits.forEach((digit, i) => {
                    if (boxes[index + i]) {
                        boxes[index + i].value = digit;
                    }
                });
                combineOTP();
            }
        });

        box.addEventListener('input', (e) => {
            const value = e.target.value;
            if (!/^\d*$/.test(value)) {
                e.target.value = "";
                return;
            }
            if (value && index < boxes.length - 1) {
                boxes[index + 1].focus();
            }
            combineOTP();
        });

        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !box.value && index > 0) {
                boxes[index - 1].focus();
            }
        });
    });

    autofillInput.addEventListener('input', (e) => {
        const val = e.target.value.trim();
        if (val.length === 6 && /^\d+$/.test(val)) {
            val.split('').forEach((char, i) => {
                boxes[i].value = char;
            });
            combineOTP();
        }
    });

    boxes.forEach(box => {
        box.addEventListener('focus', () => {
            if (window.innerWidth < 768) autofillInput.focus();
        });
    });

    const expiryDate = new Date("<?php echo date('M d, Y H:i:s', strtotime($expiry_db)); ?>").getTime();
    const countdownTask = setInterval(function() {
        const now = new Date().getTime();
        const distance = expiryDate - now;
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        const countdownElem = document.getElementById("countdown");
        if (distance >= 0) {
            countdownElem.innerHTML = 
                (minutes < 10 ? "0" + minutes : minutes) + ":" + 
                (seconds < 10 ? "0" + seconds : seconds);
            countdownElem.style.opacity = (countdownElem.style.opacity == "0.5" ? "1" : "0.5");
        } else {
            clearInterval(countdownTask);
            countdownElem.innerHTML = "00:00";
            countdownElem.style.opacity = "1";
            const timerBox = document.getElementById("timer-box");
            timerBox.style.color = "#ff4d4d";
            timerBox.innerHTML = "Kode OTP telah kedaluwarsa!";
            document.getElementById("btn-submit").disabled = true;
            document.getElementById("resend-wrapper").style.display = "block";
            document.getElementById("change-num-link").style.display = "none";
            boxes.forEach(box => box.disabled = true);
        }
    }, 1000);
</script>
</body>
</html>