<?php
require 'vendor/autoload.php'; 
include 'koneksi.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Fungsi untuk memasukkan gambar ke sel dengan posisi tengah
function addImageToCell($path, $coordinate, $sheet) {
    if (!empty($path) && file_exists($path)) {
        $drawing = new Drawing();
        $drawing->setPath($path);
        $drawing->setHeight(75); 
        $drawing->setCoordinates($coordinate);
        
        // Offset agar gambar berada di tengah sel
        $drawing->setOffsetX(10); 
        $drawing->setOffsetY(10); 
        
        $drawing->setWorksheet($sheet);
    }
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Selesai');

// 1. Header Tabel (Ditambah REPAIR ACTION di antara KONDISI dan FOTO)
// A=NO, B=DEPT, C=LOKASI, D=USER, E=TANGGAL, F=KONDISI, G=REPAIR ACTION, H=BEFORE, I=AFTER, J=TTD U, K=TTD P, L=PDF
$headers = ['NO', 'SECTION / DEPT', 'LOKASI', 'USER', 'TANGGAL', 'KONDISI', 'PERBAIKAN', 'FOTO BEFORE', 'FOTO AFTER', 'TTD USER', 'TTD PGA', 'LIHAT PDF'];
$column = 'A';
foreach ($headers as $title) {
    $sheet->setCellValue($column . '1', $title);
    
    // Styling Header Hijau
    $sheet->getStyle($column . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
    ]);
    $column++;
}

// 2. Query SQL (Ditambah r.repair_action)
$sql = "SELECT c.*, r.foto_after, r.ttd_user, r.ttd_pga, r.repair_action 
        FROM complaints c 
        INNER JOIN repair_actions r ON c.complain_id = r.complaint_id 
        WHERE r.ttd_user != '' AND r.ttd_pga != '' 
        ORDER BY c.complain_id DESC";

$query = mysqli_query($conn, $sql);

$rowNum = 2;
$no = 1;
$tempFiles = []; 

while ($row = mysqli_fetch_assoc($query)) {
    // Isi Data Teks (A - G)
    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValue('B' . $rowNum, strtoupper($row['section_dept']));
    $sheet->setCellValue('C' . $rowNum, strtoupper($row['lokasi_kerusakan']));
    $sheet->setCellValue('D' . $rowNum, strtoupper($row['nama_user']));
    $sheet->setCellValue('E' . $rowNum, date('d/m/Y', strtotime($row['tanggal'])));
    $sheet->setCellValue('F' . $rowNum, $row['kondisi_current']);
    $sheet->setCellValue('G' . $rowNum, $row['repair_action']); // Data Repair Action
    
    // Set Tinggi Baris
    $sheet->getRowDimension($rowNum)->setRowHeight(100);

    // --- PROSES GAMBAR (Geser ke kolom H dan I) ---

    // 1. FOTO BEFORE (Kolom H)
    if (!empty($row['foto_before'])) {
        addImageToCell('uploads/before/' . $row['foto_before'], 'H' . $rowNum, $sheet);
    }

    // 2. FOTO AFTER (Kolom I)
    if (!empty($row['foto_after'])) {
        addImageToCell('uploads/after/' . $row['foto_after'], 'I' . $rowNum, $sheet);
    }

    // 3. TTD USER (Kolom J)
    if (!empty($row['ttd_user'])) {
        $ttdData = explode(',', $row['ttd_user']);
        if(isset($ttdData[1])) {
            $decoded = base64_decode($ttdData[1]);
            $tempPath = 'uploads/temp_u_'.$rowNum.'_'.time().'.png';
            file_put_contents($tempPath, $decoded);
            addImageToCell($tempPath, 'J' . $rowNum, $sheet);
            $tempFiles[] = $tempPath;
        }
    }

    // 4. TTD PGA (Kolom K)
    if (!empty($row['ttd_pga'])) {
        $pgaData = explode(',', $row['ttd_pga']);
        if(isset($pgaData[1])) {
            $decodedPga = base64_decode($pgaData[1]);
            $tempPathPga = 'uploads/temp_p_'.$rowNum.'_'.time().'.png';
            file_put_contents($tempPathPga, $decodedPga);
            addImageToCell($tempPathPga, 'K' . $rowNum, $sheet);
            $tempFiles[] = $tempPathPga;
        }
    }

    // --- PROSES LINK PDF (Kolom L) ---
    $pdfUrl = "http://192.168.1.3/complain-facility/cetak.php?id=" . $row['complain_id'];
    
    $sheet->setCellValue('L' . $rowNum, "LIHAT PDF");
    $sheet->getCell('L' . $rowNum)->getHyperlink()->setUrl($pdfUrl);
    
    // Styling khusus Link (Biru & Underline)
    $sheet->getStyle('L' . $rowNum)->applyFromArray([
        'font' => [
            'color' => ['rgb' => '0000FF'],
            'underline' => true,
            'bold' => true
        ]
    ]);
    
    // Styling Border dan Alignment untuk seluruh baris (A-L)
    $sheet->getStyle('A' . $rowNum . ':L' . $rowNum)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'wrapText' => true
        ]
    ]);

    $rowNum++;
}

// 3. Atur Lebar Kolom
foreach (range('A','E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
$sheet->getColumnDimension('F')->setWidth(30); // Kondisi Current
$sheet->getColumnDimension('G')->setWidth(30); // Repair Action
$sheet->getColumnDimension('H')->setWidth(25); // Foto Before
$sheet->getColumnDimension('I')->setWidth(25); // Foto After
$sheet->getColumnDimension('J')->setWidth(20); // TTD User
$sheet->getColumnDimension('K')->setWidth(20); // TTD PGA
$sheet->getColumnDimension('L')->setWidth(15); // Link PDF

// 4. Output Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Laporan_Selesai_Facility_'.date('d-m-Y').'.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// 5. Hapus file temp ttd
foreach($tempFiles as $f) { 
    if(file_exists($f)) unlink($f); 
}
exit;