<?php
session_start();
include 'koneksi.php';

$role_login = $_SESSION['role'];
$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';

$sql = "SELECT c.*, r.foto_after, r.tanda_tangan_pic, r.ttd_user, r.ttd_pga 
        FROM complaints c 
        LEFT JOIN repair_actions r ON c.id = r.complaint_id";

if($search != ""){
    $sql .= " WHERE c.section_dept LIKE '%$search%' OR c.nama_user LIKE '%$search%' OR c.kondisi_current LIKE '%$search%'";
}

$sql .= " ORDER BY c.id DESC";
$q = mysqli_query($conn, $sql);

if(mysqli_num_rows($q) > 0){
    $no = 1;
    while($row = mysqli_fetch_array($q)){
        $has_pic  = !empty($row['tanda_tangan_pic']);
        $has_user = !empty($row['ttd_user']);
        $has_pga  = !empty($row['ttd_pga']);
        $is_done  = ($has_pic && $has_pga);
        
        echo "<tr>
                <td>".$no++."</td>
                <td>".htmlspecialchars($row['section_dept'])."</td>
                <td>".htmlspecialchars($row['nama_user'])."</td>
                <td>".date('d/m/Y', strtotime($row['tanggal']))."</td>
                <td style='text-align: left;'>".nl2br(htmlspecialchars($row['kondisi_current']))."</td>
                <td>
                    <div class='img-container'>";
                        if(!empty($row['foto_before'])) echo "<img src='uploads/".$row['foto_before']."' width='40' height='40'>";
                        if(!empty($row['foto_after'])) echo "<img src='uploads/".$row['foto_after']."' width='40' height='40' style='border-color: #28a745;'>";
        echo "      </div>
                </td>
                <td>
                    <span class='badge ".($is_done ? 'bg-done' : 'bg-pending')."'>".($is_done ? 'Done' : 'Pending')."</span>
                    <div class='check-list'>
                        <span class='".($has_pic ? 'active' : '')."'>✔PIC</span>
                        <span class='".($has_user ? 'active' : '')."'>✔User</span>
                        <span class='".($has_pga ? 'active' : '')."'>✔PGA</span>
                    </div>
                </td>
                <td>
                    <a href='edit.php?id=".$row['id']."' class='btn-link'>Update</a> | 
                    <a href='cetak.php?id=".$row['id']."' target='_blank' class='btn-link'>PDF</a>";
                    if($role_login == 'admin'){
                        echo " | <a href='proses.php?hapus=".$row['id']."' class='btn-link btn-delete' onclick='return confirm(\"Hapus?\")'>Hapus</a>";
                    }
        echo "  </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='8'>Data tidak ditemukan.</td></tr>";
}
?>