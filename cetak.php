<?php
ini_set('memory_limit', '512M');
ob_start();

require_once 'dompdf/autoload.inc.php'; 
include 'koneksi.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$id = mysqli_real_escape_string($conn, $_GET['id']);

$sql = "SELECT c.*, r.repair_action, r.foto_after, r.ttd_user, r.ttd_pga 
        FROM complaints c 
        LEFT JOIN repair_actions r ON c.complain_id = r.complaint_id 
        WHERE c.complain_id='$id'";
$query = mysqli_query($conn, $sql);
$d = mysqli_fetch_array($query);

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

$base_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$upload_dir = $base_dir . "uploads" . DIRECTORY_SEPARATOR;
$logo_path = $base_dir . "bahan/logo.png";

function imageToBase64($path) {
    if (!empty($path) && file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = @file_get_contents($path);
        if ($data) {
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }
    return false;
}

$logo_base64 = imageToBase64($logo_path);

$img_before_base64 = imageToBase64($upload_dir . "before" . DIRECTORY_SEPARATOR . $d['foto_before']);
$img_after_base64  = imageToBase64($upload_dir . "after" . DIRECTORY_SEPARATOR . $d['foto_after']);

function getSig($data) {
    if (!empty($data) && strlen($data) > 100) {
        return '<img src="'.$data.'" class="sig-img">';
    }
    return '';
}

$html = '
<!DOCTYPE html>
<html>
<head>
<style>
    @page { margin: 0.8cm; }
    body { font-family: "Helvetica", Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
    .header-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
    .logo-img { width: 150px; }
    .title { text-align: right; font-size: 20px; font-weight: bold; color: #1a2a40; }
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .info-table td { border: 1px solid #ccc; padding: 7px 10px; vertical-align: top; }
    .bg-grey { background-color: #f5f5f5; font-weight: bold; width: 110px; text-transform: uppercase; }
    .photo-table { width: 100%; border-collapse: collapse; border: 1px solid #ccc; }
    .photo-table td { width: 50%; border: 1px solid #ccc; text-align: center; vertical-align: middle; padding: 10px; height: 180px; }
    .photo-label { background: #eee; display: block; padding: 5px; font-weight: bold; font-size: 10px; margin: -10px -10px 10px -10px; border-bottom: 1px solid #ccc; text-transform: uppercase; }
    .img-content { max-width: 240px; max-height: 160px; object-fit: contain; }
    .sig-table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
    .sig-table td { border: 1px solid #ccc; text-align: center; width: 50%; vertical-align: middle; }
    .sig-header { background: #f5f5f5; font-weight: bold; font-size: 10px; padding: 5px; text-transform: uppercase; }
    .sig-box { height: 90px; padding: 5px; }
    .sig-img { max-height: 80px; max-width: 100%; display: block; margin: 0 auto; }
    .sig-footer { font-weight: bold; padding: 5px; font-size: 10px; text-transform: uppercase; background: #f5f5f5; }
    .caps { text-transform: uppercase; }
</style>
</head>
<body>

<table class="header-table">
    <tr>
        <td>'.($logo_base64 ? '<img src="'.$logo_base64.'" class="logo-img">' : '').'</td>
        <td class="title">COMPLAIN FACILITY</td>
    </tr>
</table>

<table class="info-table">
    <tr>
        <td class="bg-grey">SECTION / DEPT</td>
        <td width="35%" class="caps">'.htmlspecialchars($d['section_dept']).'</td>
        <td class="bg-grey" width="15%">TANGGAL</td>
        <td>'.date('d M Y', strtotime($d['tanggal'])).'</td>
    </tr>
    <tr>
        <td class="bg-grey">PELAPOR (USER)</td>
        <td colspan="3" class="caps">'.htmlspecialchars($d['nama_user']).'</td>
    </tr>
    <tr>
        <td class="bg-grey">LOKASI</td>
        <td colspan="3" class="caps">'.htmlspecialchars($d['lokasi_kerusakan']).'</td>
    </tr>
    <tr>
        <td class="bg-grey" style="height: 35px;">CONDITION</td>
        <td colspan="3" class="caps">'.nl2br(htmlspecialchars($d['kondisi_current'])).'</td>
    </tr>
    <tr>
        <td class="bg-grey" style="height: 35px;">REPAIR ACTION</td>
        <td colspan="3" class="caps">'.(!empty($d['repair_action']) ? nl2br(htmlspecialchars($d['repair_action'])) : '<span style="color:#999"><i>DALAM PROSES...</i></span>').'</td>
    </tr>
</table>

<table class="photo-table">
    <tr>
        <td>
            <span class="photo-label">CONDITION BEFORE</span>
            '.($img_before_base64 ? '<img src="'.$img_before_base64.'" class="img-content">' : '<span style="color:#ccc">NO IMAGE</span>').'
        </td>
        <td>
            <span class="photo-label">CONDITION AFTER</span>
            '.($img_after_base64 ? '<img src="'.$img_after_base64.'" class="img-content">' : '<span style="color:#ccc">NO IMAGE</span>').'
        </td>
    </tr>
</table>

<table class="sig-table">
    <tr>
        <td class="sig-header">REPORTED BY (USER)</td>
        <td class="sig-header">APPROVED BY (PGA)</td>
    </tr>
    <tr>
        <td class="sig-box">'.getSig($d['ttd_user']).'</td>
        <td class="sig-box">'.getSig($d['ttd_pga']).'</td>
    </tr>
    <tr>
        <td class="sig-footer">'.htmlspecialchars($d['nama_user']).'</td>
        <td class="sig-footer">PGA</td>
    </tr>
</table>

</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

ob_end_clean();
$dept_clean = str_replace(' ', '_', $d['section_dept']); 
$tanggal_file = date('dmY', strtotime($d['tanggal']));
$filename = "Report_" . $dept_clean . "_" . $tanggal_file . ".pdf";

$dompdf->stream($filename, ["Attachment" => 0]); 
?>