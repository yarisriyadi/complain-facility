# 🏢 Complain Facility System
> **Cara cerdas kelola laporan kerusakan dan bikin dokumen maintenance otomatis.**

![Status](https://img.shields.io/badge/Status-Active-brightgreen?style=for-the-badge)
![Version](https://img.shields.io/badge/Version-1.0.0-blue?style=for-the-badge)
![License](https://img.shields.io/badge/License-Copyrighted-red?style=for-the-badge)

---

## 📝 Tentang Proyek
**Complain Facility** hadir untuk membuang cara lama yang ribet dalam menangani kerusakan fasilitas di lingkungan kerja maupun operasional. Platform berbasis web ini mengintegrasikan seluruh alur pelaporan keluhan secara *end-to-end*, menghubungkan **User** (pelapor), **Teknisi** (eksekutor), hingga **Manajemen / PGA (Property & General Affairs)** secara digital dan transparan.

Enggak ada lagi laporan yang hilang, terselip, atau terlupakan. Melalui sistem ini, status setiap tiket perbaikan dapat dipantau secara *real-time*, didukung bukti dokumentasi visual yang valid, serta dilengkapi dengan fitur tanda tangan digital untuk memastikan akuntabilitas kerja sebelum sistem menghasilkan dokumen formal berbasis PDF secara otomatis.

---

## ✨ Fitur Jagoan
* **🛠️ Simple Ticketing System** – Alur pelaporan yang runut dan terstruktur, mulai dari pembuatan tiket, peninjauan, proses pengerjaan, hingga status selesai.
* **📸 Bukti Visual (Before & After)** – Teknisi dan pelapor wajib mengunggah foto kondisi fasilitas sebelum diperbaiki dan setelah penanganan untuk transparansi penuh.
* **✍️ Tanda Tangan Digital Terintegrasi** – Fitur verifikasi langsung di layar aplikasi untuk User, Teknisi, dan Tim PGA sebagai bukti serah terima pekerjaan yang sah.
* **📄 Cetak Laporan PDF Otomatis** – Menggunakan engine *Dompdf* untuk menghasilkan lembar dokumen maintenance formal, siap cetak atau diarsipkan tanpa perlu rekap manual.
* **📊 Dashboard Monitoring Intuitif** – Visualisasi data statistik mengenai jumlah laporan masuk, laporan dalam proses, dan laporan selesai dalam satu halaman utama.

---

## 🚀 Alur Kerja Sistem & Screenshot Aplikasi

Berikut adalah penjelasan detail mengenai alur operasional sistem **Complain Facility**, lengkap dengan tempat peletakan representasi visual dari setiap tahapan proses:

### 1. Sistem Autentikasi & Hak Akses (Login & Registrasi)
Sebelum masuk ke sistem, pengguna harus mengonfirmasi identitas mereka melalui halaman autentikasi. Sistem ini mendukung multi-role dengan batasan hak akses yang ketat (User Biasa, Teknisi, dan PGA).
* **Alur:** Pengguna memasukkan username dan password. Sistem memvalidasi akun dan mengarahkan pengguna ke dashboard yang sesuai dengan role mereka. Bagi pengguna baru (karyawan), registrasi dibatasi secara khusus hanya untuk role *Standard User*.