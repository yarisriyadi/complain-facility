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
    
    // MENGAMBIL DATA USER BERDASARKAN EMAIL
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($cek_user) > 0) {
        $data_user = mysqli_fetch_assoc($cek_user);
        
        /* PERUBAHAN DI SINI:
           Ganti 'nama_lengkap' sesuai dengan nama kolom nama lengkap di tabel database Anda 
        */
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
                        .apple-link-fix a { color: inherit !important; text-decoration: none !important; }
                    </style>
                </head>
                <body style='margin: 0; padding: 0; background-color: #ffffff; font-family: sans-serif;'>
                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                        <tr>
                            <td align='center' style='padding: 20px 10px;'>
                                <table width='350' border='0' cellspacing='0' cellpadding='0'>
                                    <tr>
                                        <td style='color: #333; font-size: 14px; line-height: 1.5;'>
                                            
                                            <p style='margin: 0 0 15px 0;'>Halo, <strong>" . htmlspecialchars($nama_lengkap) . "</strong></p>
                                            <p style='margin: 0 0 20px 0;'>Berikut adalah kode verifikasi Anda:</p>
                                            
                                            <div style='text-align: center; margin: 25px 0;'>
                                                <span style='font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #007bff; display: block;'>$otp</span>
                                            </div>

                                            <div style='font-size: 12px; color: #777; margin-top: 25px; line-height: 1.6; border-top: 1px solid #f2f2f2; padding-top: 15px;'>
                                                Kode ini hanya berlaku selama <strong>2 menit</strong>. Jika Anda tidak merasa meminta kode ini, mohon abaikan saja email ini. <strong>Mohon jaga kerahasiaan kode Anda.</strong>
                                                <div style='display:none; font-size:1px; line-height:1px; max-height:0px; max-width:0px; opacity:0; overflow:hidden;'>Ref: $timestamp</div>
                                            </div>

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