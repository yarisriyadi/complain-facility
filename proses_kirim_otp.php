<?php
session_start();
set_time_limit(120); 

if (file_exists('config_maintenance.php')) {
    require_once 'config_maintenance.php';
}

if (isset($maintenance_mode) && $maintenance_mode === true) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: maintenance.php");
        exit();
    }
}

include "koneksi.php";

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "
<!DOCTYPE html>
<html>
<head>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: transparent; }
        /* Style Dark Mode untuk Swal */
        .dark-popup { background: #2c2c2c !important; color: #fff !important; }
        .dark-title { color: #fff !important; }
    </style>
    <script>
        function getSwalConfig() {
            const theme = localStorage.getItem('selected-theme') || 'dark';
            return {
                background: theme === 'dark' ? '#1e1e1e' : '#ffffff',
                color: theme === 'dark' ? '#ffffff' : '#333333'
            };
        }
    </script>
</head>
<body>";

if (isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($cek_user) > 0) {
        $data_user = mysqli_fetch_assoc($cek_user);
        $nama_lengkap = !empty($data_user['nama_lengkap']) ? $data_user['nama_lengkap'] : 'User'; 

        $otp = rand(100000, 999999);
        date_default_timezone_set('Asia/Jakarta');
        $expiry = date("Y-m-d H:i:s", strtotime("+2 minutes"));

        $update = mysqli_query($conn, "UPDATE users SET otp_code = '$otp', otp_expiry = '$expiry' WHERE email = '$email'");

        if ($update) {
            $_SESSION['email_reset'] = $email;
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'pthtmi123@gmail.com'; 
                $mail->Password   = 'xuvalxykuepbpblc'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
                $mail->Port       = 465; 

                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $mail->setFrom('pthtmi123@gmail.com', 'SHINSEI SYSTEM');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Kode OTP Verifikasi - SHINSEI';
                $mail->Body = "
<!DOCTYPE html>
<html>
<head>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body, table, td { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important; }
        .apple-link-fix a { color: inherit !important; text-decoration: none !important; }
    </style>
</head>
<body style='margin: 0; padding: 0; background-color: #f4f7f9; -webkit-text-size-adjust: 100%;'>
    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f4f7f9;'>
        <tr>
            <td align='center' style='padding: 50px 10px;'>
                <table width='100%' border='0' cellspacing='0' cellpadding='0' style='max-width: 480px; background-color: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 4px 10px rgba(0,0,0,0.08); overflow: hidden;'>
                    
                    <tr>
                        <td align='center' style='background-color: #0056b3; padding: 25px 20px; border-radius: 8px 8px 0 0;'>
                            <span style='color: #ffffff; font-size: 26px; font-weight: 800; letter-spacing: 1px; display: block;'>
                                COMPLAIN FACILITY
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td style='padding: 40px;'>
                            <p style='margin: 0 0 10px 0; font-size: 16px; color: #333;'>
                                Halo <strong>" . htmlspecialchars($nama_lengkap) . "</strong>,
                            </p>
                            <p style='margin: 0 0 20px 0; font-size: 14px; color: #4a5568; line-height: 1.6;'>
                                Berikut adalah kode OTP Anda untuk melanjutkan proses perubahan password di sistem <strong>Complain Facility</strong>:
                            </p>
                            
                            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin: 25px 0;'>
                                <tr>
                                    <td align='center' style='background-color: #ebf4ff; border: 2px dashed #bee3f8; padding: 20px; border-radius: 8px;'>
                                        <span style='font-size: 42px; font-weight: 800; letter-spacing: 10px; color: #0056b3; display: block;'>$otp</span>
                                    </td>
                                </tr>
                            </table>

                            <p style='font-size: 13px; color: #718096; text-align: center; margin-bottom: 25px;'>
                                Berlaku selama <span style='color: #000000; font-weight: 600;'>2 menit</span>.
                            </p>

                            <div style='background-color: #fff5f5; border-left: 4px solid #f56565; padding: 15px; border-radius: 4px;'>
                                <p style='margin: 0; font-size: 12px; color: #c53030; line-height: 1.6;'>
                                    <strong>PENTING:</strong> Jika Anda tidak merasa meminta perubahan password, abaikan email ini dan <strong>segera hubungi tim IT</strong>. Jangan berikan kode ini kepada siapapun.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style='padding: 0 40px 30px 40px;'>
                            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='border-top: 1px solid #edf2f7; padding-top: 20px;'>
                                <tr>
                                    <td style='font-size: 11px; color: #a0aec0; text-align: center; line-height: 1.6;'>
                                        IT Department - PT. Shinsei Denshi Indonesia.<br>
                                        Sent by System Auto-Mailer
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>";

                $mail->AltBody = "Halo, $nama_lengkap Kode OTP Anda adalah: $otp. Kode berlaku selama 2 menit.";

                if($mail->send()){
                    echo "<script>
                        const cfg = getSwalConfig();
                        Swal.fire({
                            icon: 'success',
                            title: 'OTP Terkirim!',
                            text: 'Silakan periksa email Anda.',
                            background: cfg.background,
                            color: cfg.color,
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            window.location='verifikasi.php?pesan=terkirim';
                        });
                    </script>";
                    exit();
                }

            } catch (Exception $e) {
                echo "<script>
                    const cfg = getSwalConfig();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Kirim!',
                        text: 'Error: {$mail->ErrorInfo}',
                        background: cfg.background,
                        color: cfg.color,
                        confirmButtonColor: '#d33'
                    }).then(() => {
                        window.location='lupa_password.php';
                    });
                </script>";
            }
        }
    } else {
        echo "<script>
            const cfg = getSwalConfig();
            Swal.fire({
                icon: 'warning',
                title: 'Email Tidak Ada!',
                text: 'Email tidak terdaftar di sistem.',
                background: cfg.background,
                color: cfg.color,
                confirmButtonColor: '#ffc107'
            }).then(() => {
                window.location='lupa_password.php';
            });
        </script>";
    }
} else {
    header("Location: lupa_password.php");
}
echo "</body></html>";
?>