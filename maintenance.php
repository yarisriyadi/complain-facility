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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }

        body { 
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh; overflow: hidden; 
        }

        #video-bg { 
            position: fixed; right: 0; bottom: 0; 
            min-width: 100%; min-height: 100%; 
            z-index: -1; object-fit: cover; filter: brightness(0.6); 
        }

        .card { 
            background: rgba(255, 255, 255, 0.15); 
            backdrop-filter: blur(10px); 
            -webkit-backdrop-filter: blur(10px); 
            padding: 40px; border-radius: 16px; 
            border: 1px solid rgba(255, 255, 255, 0.2); 
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3); 
            width: 90%; max-width: 420px; 
            text-align: center; position: relative; z-index: 1;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }

        .img-container img {
            width: 160px;
            height: auto;
            margin-bottom: 15px;
            filter: drop-shadow(0px 4px 10px rgba(0, 0, 0, 0.3));
        }

        .badge {
            display: inline-block;
            background: rgba(255, 94, 94, 0.2);
            color: #ff9f9f;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 94, 94, 0.3);
        }

        h1 {
            font-size: 26px;
            margin-bottom: 15px;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        p {
            font-size: 14px;
            color: #f0f0f0;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-countdown { 
            width: 100%; padding: 14px; 
            background-color: #2c3e50; /* Warna gelap sesuai gambar */
            color: white; border: none; 
            border-radius: 30px; /* Lebih lonjong sesuai gambar */
            font-size: 15px; 
            text-transform: none; text-decoration: none;
            display: inline-block;
            pointer-events: none; /* Tidak bisa di-klik */
            cursor: default;
        }

        /* Warna angka kuning sesuai gambar */
        #timer {
            color: #ffc107; 
            font-weight: bold;
            margin: 0 5px;
        }

        footer {
            margin-top: 35px;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 15px;
        }
    </style>
</head>
<body>

    <video autoplay muted loop playsinline id="video-bg">
        <source src="bahan/dark.mp4" type="video/mp4">
    </video>

    <div class="card">
        <div class="img-container">
            <img src="bahan/robot.png" alt="Maintenance Robot" onerror="this.src='https://cdn-icons-png.flaticon.com/512/4439/4439866.png'">
        </div>

        <div class="badge"><i class="fa-solid fa-gears"></i> Sistem Pemeliharaan</div>

        <h1>Sesi Dibatalkan</h1>
        
        <p>Sistem sedang dalam perbaikan rutin. Demi keamanan, <b>data input Anda tidak disimpan</b>. Mohon baca pesan ini sebelum sistem dialihkan.</p>

        <div class="btn-countdown">
            Kembali ke Login dalam <span id="timer">30</span> detik
        </div>

        <footer>
            IT Department - PT. SHINSEI DENSHI INDONESIA
        </footer>
    </div>

    <script>
    let timeleft = 30;
    const timerElement = document.getElementById("timer");

    const countdownTimer = setInterval(function(){
        timeleft -= 1;
        
        // Update angka di layar
        if(timerElement) {
            timerElement.innerText = timeleft;
        }

        if(timeleft <= 0){
            clearInterval(countdownTimer);
            // Menggunakan replace agar user tidak bisa 'Back' ke halaman ini lagi
            window.location.replace("login.php"); 
        }
    }, 1000);
</script>
</body>
</html>