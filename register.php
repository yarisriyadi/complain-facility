<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - SHINSEI</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style_theme.css">
    
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 0; padding: 0; 
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh; overflow-x: hidden;
        }

        .reg-container { 
            background: var(--container-bg); 
            backdrop-filter: blur(10px); 
            -webkit-backdrop-filter: blur(10px); 
            padding: 35px; border-radius: 16px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px var(--shadow); 
            width: 90%; max-width: 380px; 
            box-sizing: border-box; position: relative; z-index: 1; margin: 20px 0;
            transition: all 0.4s ease;
        }

        .reg-container h2, .reg-container label, .login-link, .copyright {
            color: var(--text-color);
            transition: color 0.4s ease;
        }

        .reg-container h2 { 
            text-align: center; margin: 0 0 25px 0; font-size: 26px; 
            font-weight: bold; letter-spacing: 2px; text-transform: uppercase;
        }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: bold; }

        .form-group input { 
            width: 100%; padding: 12px; border: 1px solid var(--border-color); 
            border-radius: 8px; box-sizing: border-box; font-size: 15px; 
            background: var(--input-bg); color: #333; transition: 0.3s;
        }

        .btn-reg { 
            width: 100%; padding: 14px; background-color: #28a745; border: none; 
            color: white; border-radius: 8px; cursor: pointer; font-size: 16px; 
            font-weight: bold; margin-top: 10px; transition: all 0.4s ease; 
            text-transform: uppercase; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-reg:hover { 
            background-color: #218838; transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.5);
        }

        .login-link { text-align: center; margin-top: 25px; font-size: 14px; }
        /* WARNA LINK LOGIN DIUBAH JADI BIRU DI SINI */
        .login-link a { color: #007bff !important; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .login-link a:hover { text-decoration: underline; color: #0056b3 !important; }

        .copyright { 
            text-align: center; margin-top: 30px; font-size: 10px; 
            border-top: 1px solid var(--border-color); padding-top: 15px; 
        }

        .password-container { position: relative; width: 100%; }
        .toggle-password { 
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%); 
            cursor: pointer; color: #666; font-size: 18px; z-index: 10; 
        }

        .msg-error { font-size: 11px; margin-top: 5px; font-weight: bold; color: #ff4d4d; }
        .msg-success { font-size: 11px; margin-top: 5px; font-weight: bold; color: #2ecc71; }
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

    <div class="reg-container">
        <h2>REGISTRASI</h2>
        <form action="proses_register.php" method="POST" id="regForm" onsubmit="return handleRegistration(event)">
            <input type="hidden" name="device_id" id="device_id">
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="username" required autocomplete="off" placeholder="Username">
                <div id="user-message"></div>
            </div>
            
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" required placeholder="Nama Sesuai ID Card">
                <div id="name-message"></div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" required placeholder="Masukan Email Aktif" autocomplete="off">
                <div id="email-message"></div> 
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <div class="password-container">
                    <input type="password" name="password" id="password" required placeholder="Masukan Password">
                    <i class="fa-solid fa-eye-slash toggle-password"></i>
                </div>
                <div id="pass-message"></div>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <div class="password-container">
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Ulangi password">
                    <i class="fa-solid fa-eye-slash toggle-password"></i>
                </div>
                <div id="confirm-message"></div>
            </div>

            <button type="submit" class="btn-reg">REGISTRASI</button>
        </form>
        
        <div class="login-link">
            Sudah punya Akun? <a href="login.php">Login</a>
        </div>
        <div class="copyright">&copy; 2026 PT. Shinsei Denshi Indonesia.</div>
    </div>

    <script src="https://openfpcdn.io/fingerprintjs/v4/i.js"></script>
    <script src="theme_script.js"></script>
    <script>
    // ... FingerprintJS tetap sama ...
    const fpPromise = import('https://openfpcdn.io/fingerprintjs/v4').then(FingerprintJS => FingerprintJS.load())
    fpPromise.then(fp => fp.get()).then(result => {
        document.getElementById('device_id').value = result.visitorId;
    })

    const usernameInput = document.getElementById('username');
    const nameInput = document.getElementById('nama_lengkap');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    
    const userMsg = document.getElementById('user-message');
    const nameMsg = document.getElementById('name-message');
    const emailMsg = document.getElementById('email-message');
    const passMsg = document.getElementById('pass-message');
    const confirmMsg = document.getElementById('confirm-message');

    let isUserValid = false, isNameValid = false, isEmailValid = false, isPassValid = false, isConfirmValid = false;

    function validateForm() {
        const user = usernameInput.value;
        const name = nameInput.value;
        
        // --- NORMALISASI EMAIL OTOMATIS ---
        // Mengubah input email menjadi huruf kecil secara otomatis saat mengetik
        emailInput.value = emailInput.value.toLowerCase(); 
        const email = emailInput.value;

        // Validasi Username
        if (user === "") { userMsg.innerHTML = ""; isUserValid = false; }
        else if (!/^[A-Z]/.test(user)) {
            userMsg.className = "msg-error";
            userMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Diawali Huruf Besar!';
            isUserValid = false;
        } else if (user.length < 3) {
            userMsg.className = "msg-error";
            userMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Minimal 3 karakter!';
            isUserValid = false;
        } else {
            userMsg.className = "msg-success";
            userMsg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Username Oke';
            isUserValid = true;
        }

        // Validasi Nama
        if (name === "") { nameMsg.innerHTML = ""; isNameValid = false; }
        else if (!/^[A-Z]/.test(name)) {
            nameMsg.className = "msg-error";
            nameMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Diawali Huruf Besar!';
            isNameValid = false;
        } else {
            nameMsg.className = "msg-success";
            nameMsg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Nama Oke';
            isNameValid = true;
        }

        // Validasi Email
        if (email === "") { emailMsg.innerHTML = ""; isEmailValid = false; }
        else if (!email.endsWith("@gmail.com")) {
            emailMsg.className = "msg-error";
            emailMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Wajib gunakan @gmail.com!';
            isEmailValid = false;
        } else {
            emailMsg.className = "msg-success";
            emailMsg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Email Oke';
            isEmailValid = true;
        }

        // Validasi Password
        const hasSymbol = /[!@#$%^&*(),.?":{}|<>_]/.test(passwordInput.value);
        const hasNumber = /[0-9]/.test(passwordInput.value);
        const pass = passwordInput.value;

        if (pass === "") { passMsg.innerHTML = ""; isPassValid = false; }
        else if (!/^[A-Z]/.test(pass)) {
            passMsg.className = "msg-error";
            passMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Diawali Huruf Besar!';
            isPassValid = false;
        } else if (pass.length < 6) {
            passMsg.className = "msg-error";
            passMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Minimal 6 karakter!';
            isPassValid = false;
        } else if (!hasNumber || !hasSymbol) {
            passMsg.className = "msg-error";
            passMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Wajib ada Angka & Simbol!';
            isPassValid = false;
        } else {
            passMsg.className = "msg-success";
            passMsg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Password Kuat';
            isPassValid = true;
        }

        // Validasi Konfirmasi Password
        const confirm = confirmInput.value;
        if (confirm === "") { confirmMsg.innerHTML = ""; isConfirmValid = false; }
        else if (confirm !== pass) {
            confirmMsg.className = "msg-error";
            confirmMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Tidak cocok!';
            isConfirmValid = false;
        } else {
            confirmMsg.className = "msg-success";
            confirmMsg.innerHTML = '<i class="fa-solid fa-circle-check"></i> Password Cocok';
            isConfirmValid = true;
        }
    }

    function handleRegistration(e) {
        validateForm();
        if (!isUserValid || !isNameValid || !isEmailValid || !isPassValid || !isConfirmValid) {
            e.preventDefault();
            alert("Periksa kembali inputan Anda!");
            return false;
        }
        return true;
    }

    [usernameInput, nameInput, emailInput, passwordInput, confirmInput].forEach(el => {
        el.addEventListener('keyup', validateForm);
        el.addEventListener('blur', validateForm);
    });

    document.querySelectorAll('.toggle-password').forEach(item => {
        item.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });
    });
</script>
</body>
</html>