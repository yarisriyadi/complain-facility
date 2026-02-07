<?php 
include 'koneksi.php';
$id = mysqli_real_escape_string($conn, $_GET['id']);
$sql = "SELECT c.*, r.foto_after FROM complaints c 
        LEFT JOIN repair_actions r ON c.id = r.complaint_id 
        WHERE c.id=$id";
$row = mysqli_fetch_array(mysqli_query($conn, $sql));

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/complain_facility/uploads/";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Print Report #<?php echo $id; ?></title>
    <style>
        body { 
        font-family: Arial, sans-serif; 
        padding: 30px; 
        line-height: 1.6; 
    }
        .header { 
        text-align: center; 
        border-bottom: 3px double #000; 
        margin-bottom: 20px; 
        padding-bottom: 10px; 
    }
        table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-bottom: 20px; 
    }
        th, td { 
        border: 1px solid black; 
        padding: 12px; 
        text-align: left; 
    }
        .label { 
        background-color: #f2f2f2; 
        font-weight: bold; 
        width: 150px; 
    }
        .img-box { 
        text-align: center; 
    }
        .img-box img { 
        max-width: 250px; 
        border: 1px solid #ccc; 
        margin-top: 10px; 
    } 
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
    }
    </style>
</head>
<body>
    <div class="header">
        <h1>COMPLAIN FACILITY REPORT</h1>
    </div>

    <table>
        <tr>
            <td class="label">Section / Dept</td>
            <td><?php echo $row['section_dept']; ?></td>
            <td class="label">Tanggal</td>
            <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
        </tr>
        <tr>
            <td class="label">Pelapor</td>
            <td colspan="3"><?php echo $row['nama_user']; ?></td>
        </tr>
        <tr>
            <td class="label">Kondisi / Masalah</td>
            <td colspan="3"><?php echo nl2br($row['kondisi_current']); ?></td>
        </tr>
    </table>

    <table>
        <tr style="background:#f2f2f2; text-align:center; font-weight:bold;">
            <td>FOTO BEFORE</td>
            <td>FOTO AFTER</td>
        </tr>
        <tr>
            <td class="img-box">
                <?php if($row['foto_before']) { ?>
                    <img src="uploads/<?php echo $row['foto_before']; ?>">
                <?php } else { echo "Tidak ada foto"; } ?>
            </td>
            <td class="img-box">
                <?php if($row['foto_after']) { ?>
                    <img src="uploads/<?php echo $row['foto_after']; ?>">
                <?php } else { echo "Belum diperbaiki"; } ?>
            </td>
        </tr>
    </table>

    <p style="font-size: 10px; color: #666;">Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>

    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() { window.close(); }, 500);
        };
    </script>
</body>
</html>