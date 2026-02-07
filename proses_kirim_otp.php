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
                $mail->Password   = 'qcknezwknlrwvpeb'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
                $mail->Port       = 465; 

                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $mail->setFrom('pthtmi123@gmail.com', 'Complain Facility');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Kode OTP Verifikasi - SHINSEI';

                $timestamp = date("H:i:s");

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
                <table width='100%' border='0' cellspacing='0' cellpadding='0' style='max-width: 480px; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);'>
                    
                    <tr>
                        <td style='background-color: #0056b3; height: 6px; border-radius: 8px 8px 0 0;'></td>
                    </tr>

                    <tr>
                        <td style='padding: 40px;'>

                            <p style='margin: 0 0 10px 0; font-size: 15px; color: #333;'>
                                Halo <strong>" . htmlspecialchars($nama_lengkap) . "</strong>,
                            </p>
                            <p style='margin: 0 0 20px 0; font-size: 14px; color: #4a5568; line-height: 1.5;'>
                                Berikut adalah kode OTP Anda untuk melanjutkan proses perubahan password di sistem :
                            </p>
                            
                            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin: 25px 0;'>
                                <tr>
                                    <td align='center' style='background-color: #ebf4ff; border: 1px solid #bee3f8; padding: 20px; border-radius: 6px;'>
                                        <span style='font-size: 42px; font-weight: 700; letter-spacing: 10px; color: #000000; display: block;'>$otp</span>
                                    </td>
                                </tr>
                            </table>

                            <p style='font-size: 13px; color: #718096; text-align: center; margin-bottom: 25px;'>
                                Berlaku selama <span style='color: #000000; font-weight: 600;'>2 menit</span>.
                            </p>

                            <div style='background-color: #fff5f5;'>
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
                                    <td style='font-size: 11px; color: #a0aec0; text-align: center; line-height: 1.5;'>
                                        IT Department - PT. Shinsei Denshi Indonesia.<br>
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
                    echo "<script>alert('OTP Berhasil Dikirim!'); window.location='verifikasi.php?pesan=terkirim';</script>";
                    exit();
                }

            } catch (Exception $e) {
                echo "Gagal mengirim email: {$mail->ErrorInfo}";
                echo "<br><br><a href='lupa_password.php'>Kembali</a>";
            }
        }
    } else {
        echo "<script>alert('Email tidak terdaftar!'); window.location='lupa_password.php';</script>";
    }
} else {
    header("Location: lupa_password.php");
}
?>