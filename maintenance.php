<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAINTENANCE - SHINSEI COMPLAIN</title>
    
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
            background-color: var(--body-bg); 
            transition: background 0.4s ease;
        }
        .maintenance-container { 
            background: var(--container-bg); 
            padding: 45px 40px; 
            border-radius: 16px; 
            border: 1px solid var(--border-color); 
            box-shadow: 0 8px 32px var(--shadow); 
            width: 90%; 
            max-width: 380px; 
            box-sizing: border-box; 
            position: relative; 
            z-index: 1; 
            text-align: center;
            transition: all 0.4s ease;
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .gear-container {
            margin-bottom: 25px;
            color: var(--text-color);
            position: relative;
            display: inline-block;
        }
        .gear-main {
            font-size: 80px;
            animation: spin 4s linear infinite;
            opacity: 0.9;
        }
        .gear-small {
            font-size: 40px;
            position: absolute;
            bottom: -5px;
            right: -15px;
            animation: spin-reverse 3s linear infinite;
            color: #007bff; /* Warna aksen biru agar menarik */
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes spin-reverse {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }
        .maintenance-container h1, 
        .maintenance-container p, 
        .copyright { 
            color: var(--text-color); 
            transition: color 0.4s ease;
        }
        .badge {
            display: inline-block;
            background: var(--border-color);
            color: #e74c3c;
            padding: 8px 18px;
            border-radius: 8px; 
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        h1 {
            font-size: 24px;
            margin: 0 0 15px 0;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .maintenance-msg {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.8;
        }
        .btn-countdown { 
            width: 100%; 
            padding: 14px; 
            background-color: #007bff; 
            border: none; 
            color: white; 
            border-radius: 8px; 
            font-size: 15px; 
            font-weight: bold; 
            display: inline-block;
            pointer-events: none;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            text-transform: uppercase;
            box-sizing: border-box;
        }
        #timer {
            color: #ffc107; 
            font-weight: bold;
            margin-left: 5px;
        }
        .copyright { 
            text-align: center; 
            margin-top: 30px; 
            font-size: 10px; 
            border-top: 1px solid var(--border-color); 
            padding-top: 15px; 
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.6;
        }
        .theme-switcher {
            position: fixed;
            bottom: 25px;
            left: 25px;
            z-index: 1000;
        }
        .theme-btn {
            background: var(--container-bg);
            border: 1px solid var(--border-color);
            width: 45px;
            height: 45px;
            border-radius: 12px; 
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px var(--shadow);
            transition: all 0.3s ease;
        }
        [data-theme='dark'] #theme-icon-sun { display: none; }
        [data-theme='dark'] #theme-icon-moon { display: block; }
        [data-theme='light'] #theme-icon-sun { display: block; }
        [data-theme='light'] #theme-icon-moon { display: none; }
    </style>
</head>
<body>

    <div class="theme-switcher">
        <button class="theme-btn" onclick="toggleTheme()" title="Ganti Tema">
            <i id="theme-icon-sun" class="fa-solid fa-sun" style="color: #f1c40f;"></i>
            <i id="theme-icon-moon" class="fa-solid fa-moon" style="color: #f1c40f;"></i>
        </button>
    </div>

    <div class="maintenance-container">
        <div class="gear-container">
            <i class="fa-solid fa-gear gear-main"></i>
            <i class="fa-solid fa-gear gear-small"></i>
        </div>

        <div class="badge"><i class="fa-solid fa-screwdriver-wrench"></i> Pemeliharaan Sistem</div>

        <h1>SESI DIBATALKAN</h1>

        <p class="maintenance-msg">
            Mohon maaf atas ketidaknyamanannya. <br>
            Sistem sedang dalam perbaikan rutin. Demi keamanan, data input Anda tidak disimpan.
        </p>

        <div class="btn-countdown">
            Kembali ke Login <span id="timer">30</span>
        </div>

        <div class="copyright">IT Department - PT. SHINSEI DENSHI INDONESIA</div>
    </div>

    <script src="theme_script.js"></script>
    <script>
        let timeleft = 30;
        const timerElement = document.getElementById("timer");

        const countdownTimer = setInterval(function(){
            timeleft -= 1;
            if(timerElement) {
                timerElement.innerText = timeleft;
            }

            if(timeleft <= 0){
                clearInterval(countdownTimer);
                window.location.replace("login.php"); 
            }
        }, 1000);
    </script>
</body>
</html>