# Knowledge Base — Aplikasi CoHub IA ITB Jatim

## 1. Tentang Platform
CoHub IA ITB Jatim adalah marketplace dua arah (multi-sided marketplace) berbasis web (Laravel 11 + PHP 8.2) yang menghubungkan:
- **Customer / Penyewa** — mencari dan menyewa properti atau jasa
- **Pemilik Properti (Owner)** — menjual/menyewakan aset properti
- **Penyedia Jasa (Provider)** — menawarkan jasa profesional
- **Administrator** — verifikasi, moderasi, manajemen user & transaksi

Website resmi: https://cohubiaitbjatim.com

## 2. Role & Hak Akses
| Role | Fungsi | Dashboard |
|---|---|---|
| Customer/Penyewa | Mencari & menyewa properti/jasa | /customer/dashboard |
| Pemilik Properti | Menjual/sewa aset properti | /owner/dashboard |
| Penyedia Jasa | Menawarkan jasa profesional | /provider/dashboard |
| Administrator | Verifikasi, moderasi, user & transaksi | /admin/dashboard |

Setelah login/register, sistem otomatis mengarahkan pengguna ke dashboard sesuai role-nya.

## 3. Alur Umum Penggunaan
1. Landing Page → Login/Register → Otentikasi → Cek Role → Dashboard sesuai role.
2. Customer: Search → Filter → Detail Properti/Jasa → Booking → Transaksi.
3. Owner/Provider: Tambah listing → Admin Verification → Published → Kelola order/booking.
4. Admin: verifikasi properti & jasa, kelola user, kelola transaksi, laporan, history log, pengaturan.

## 4. Workflow Pemilik Properti (Owner)
Dashboard Owner menampilkan: Total Properti, Properti Aktif, Properti Disewa, Pending Booking, Booking Aktif, dan Pendapatan.

Alur tambah properti:
1. Pilih Kategori: Tempat Tinggal, Ritel/Usaha, Gudang/Storage, atau Kantor.
2. Isi Informasi Properti: Nama, Luas Tanah, Luas Bangunan, Deskripsi, Lokasi, Harga Sewa.
3. Upload Media: **maksimal 5 foto/video**, file pertama otomatis menjadi thumbnail.
4. Set Status: Available / Rented / Maintenance.
5. Submit → menunggu Admin Verification → jika disetujui maka Published (tampil publik).

## 5. Workflow Penyedia Jasa (Provider)
Alur tambah jasa:
1. Pilih Kategori: Interior, Cleaning, Catering, Rental Tools, atau Others.
2. Detail Jasa: Nama Jasa, Deskripsi, Harga Mulai Dari, Satuan Harga, Portfolio.
3. Atur Availability hari aktif: **Senin sampai Sabtu**.
4. Submit → Admin Verification → Published.

## 6. Workflow Customer
1. Landing page → Search (keyword + lokasi).
2. Pilih tab Properti atau Jasa.
3. Filter:
   - Properti: Lokasi, Tipe, Harga (min–max).
   - Jasa: Kategori, Kisaran harga.
4. Buka Property Detail / Service Detail (thumbnail, gallery, nama, kategori, luas tanah/bangunan, lokasi, deskripsi, harga, availability, owner).
5. Klik "Sewa Sekarang" / Booking → form booking.

## 7. Workflow Booking Properti
Customer → Pilih Properti → Pilih Tanggal (Start Date & End Date) → Tujuan Penggunaan Properti → Custom Note/Requirement → Review Booking → Submit.

Urutan status transaksi:
DRAFT → PENDING_CONFIRMATION → (Owner Review) → APPROVED → PAID → IN_PROGRESS/ACTIVE → COMPLETED.

Jalur alternatif:
- PENDING_CONFIRMATION → REJECTED → CANCELLED
- Setiap status bisa berakhir CANCELLED (sesuai aturan transisi).

Setelah disetujui owner, customer melakukan pembayaran. Setelah dibayar, owner menandai sewa berjalan (In Progress) lalu menyelesaikannya (Completed).

## 8. Workflow Booking Jasa
Service Detail → Booking Service → Pilih Jadwal (Tanggal, Jam, Durasi/Periode) → Custom Requirement → Quantity/Pax/Unit → Price Calculation (harga satuan × quantity) → Review → Submit → PENDING_CONFIRMATION → Provider Review → APPROVE → PAID → IN PROGRESS → COMPLETED, atau REJECT → CANCELLED.

## 9. Aturan Penting: Properti & Jasa Dipisah
Customer TIDAK melakukan satu checkout gabungan properti + jasa. Booking properti dan booking jasa adalah dua transaksi terpisah:
- BOOKING properti → kepada Pemilik Properti (Owner).
- BOOKING jasa → kepada Penyedia Jasa (Provider).

## 10. Notifikasi & Email
- Setiap ada request/booking dan perubahan status transaksi, sistem mengirim notifikasi (ikon lonceng 🔔 di bagian atas dashboard) ke customer, owner/provider, dan admin.
- Email otomatis dikirim via SMTP ke customer, admin, dan pemilik properti/penyedia jasa terkait.
- Admin dapat mengatur SMTP dan API key AI (Groq) dari menu Admin → Pengaturan.

## 11. Chat & AI Assistant
- Tersedia widget chat di pojok kanan bawah situs dengan dua mode:
  - **AI Assistant (Groq)** — chatbot otomatis menjawab pertanyaan seputar CoHub berdasarkan knowledge base ini, 24/7.
  - **Chat Admin** — percakapan langsung dengan admin manusia.
- Admin membalas chat dari menu Admin → Chat Masuk.

## 12. Manajemen Admin
Admin menjadi pusat kontrol sistem:
- **User Management** — kelola pengguna & role.
- **Property Management** — Pending Verification / Approved / Rejected / Moderation.
- **Service Management** — Pending Verification / Approved / Rejected / Moderation.
- **Transaction Management** — Draft, Pending, Approved, Paid, Active, Completed, Cancelled.
- **Reports** — laporan Properties, Services, Users, Transactions.
- **History Log** — audit trail seluruh aktivitas.
- **Pengaturan** — Landing page, SMTP email, branding (logo/favicon), dan Chat & AI.

## 13. Menu Halaman Aplikasi
Public: Home, Properties, Services, Login, Register.
Customer: Dashboard, Cari Properti, Cari Jasa, Booking Saya, Profil.
Owner: Dashboard, Properti Saya, Tambah Properti, Order Sewa.
Provider: Dashboard, Jasa Saya, Tambah Jasa, Order Jasa.
Admin: Dashboard, Kelola Pengguna, Verifikasi Properti, Verifikasi Jasa, Transaksi, Chat Masuk, Laporan, History Log, Pengaturan.

## 14. Harga & Perhitungan
- Properti: harga per periode (daily/bulanan/tahunan). Total = harga × jumlah unit periode (mis. sewa 60 hari harga bulanan = 2 × harga bulanan).
- Jasa: Total = harga mulai × quantity (pax/unit).

## 15. Tips Menjawab
- Jawab dalam Bahasa Indonesia yang ramah dan singkat.
- Jika ditanya di luar topik CoHub/properti/jasa/transaksi, arahkan kembali dengan sopan ke topik platform CoHub IA ITB Jatim.
- Untuk kendala akun/pembayaran spesifik, sarankan gunakan mode "Chat Admin" pada widget chat atau login ke dashboard masing-masing.
