# CoHub IA ITB Jatim

**CoHub IA ITB Jatim** adalah *multi-sided marketplace* berbasis web yang menghubungkan **Customer (Penyewa)**, **Pemilik Properti (Owner)**, **Penyedia Jasa (Provider)**, dan **Administrator** dalam satu platform untuk kebutuhan sewa properti dan jasa profesional.

Website resmi: <https://cohubiaitbjatim.com>

---

## 🧭 Tentang Platform

CoHub memfasilitasi dua jenis transaksi utama yang masing-masing terpisah:

| Jenis Transaksi | Penerima Order |
| --- | --- |
| Booking **Properti** (tempat tinggal, ritel/usaha, gudang/storage, kantor) | Pemilik Properti (Owner) |
| Booking **Jasa** (interior, cleaning, catering, rental tools, dll.) | Penyedia Jasa (Provider) |

Setiap listing (properti/jasa) yang dibuat oleh Owner/Provider harus melalui **verifikasi Admin** terlebih dahulu sebelum tampil publik (*Published*).

---

## ✨ Fitur Utama

### 🌐 Halaman Publik
- Landing page dengan pencarian (keyword + lokasi)
- Katalog & detail **Properti** (galeri media, luas tanah/bangunan, lokasi, harga per periode, status)
- Katalog & detail **Jasa** (portfolio, harga mulai dari, satuan harga, availability)
- Filter: lokasi, tipe/kategori, dan kisaran harga
- Widget **Chat** di pojok kanan bawah dengan dua mode:
  - **AI Assistant (Groq)** — chatbot otomatis 24/7 berbasis knowledge base CoHub
  - **Chat Admin** — percakapan langsung dengan admin manusia

### 👤 Customer (Penyewa)
- Dashboard ringkasan aktivitas
- Pencarian & filter properti/jasa
- Booking properti: pilih tanggal (start–end), tujuan penggunaan, catatan khusus
- Booking jasa: pilih jadwal (tanggal, jam, durasi), quantity/pax/unit dengan kalkulasi harga otomatis
- Kelola booking: submit, bayar, batalkan
- Notifikasi lonceng 🔔 untuk setiap perubahan status transaksi
- Profil & ganti password

### 🏠 Pemilik Properti (Owner)
- Dashboard statistik (total properti, aktif, disewa, pending booking, booking aktif, pendapatan)
- CRUD properti dengan kategori, spesifikasi luas, harga sewa (harian/bulanan/tahunan), dan unggah media (maks. 5 foto/video — file pertama otomatis jadi thumbnail)
- Status properti: Available / Rented / Maintenance
- Kelola order sewa: approve, reject, start (in progress), complete

### 🛠️ Penyedia Jasa (Provider)
- Dashboard statistik
- CRUD jasa: kategori, deskripsi, harga mulai dari, satuan harga, portfolio, media
- Pengaturan availability hari aktif
- Kelola order jasa: approve, reject, start, complete

### 🛡️ Administrator
- Dashboard dengan pencarian global
- **User Management** — kelola pengguna, role, aktif/nonaktif
- **Verifikasi Properti & Jasa** — pending / approved / rejected / moderation
- **Transaction Management** — kelola seluruh transaksi
- **Chat Masuk** — balas percakapan widget chat pengguna
- **Reports** — laporan properties, services, users, transactions
- **History Log** — audit trail seluruh aktivitas sistem
- **Pengaturan** — landing page, SMTP email (dengan tes kirim), branding (logo/favicon), dan konfigurasi Chat & AI (Groq)

### 🔔 Notifikasi & Email
- Notifikasi in-app (ikon lonceng) ke customer, owner/provider, dan admin pada setiap request booking dan perubahan status
- Email otomatis via SMTP ke semua pihak terkait — konfigurasi dapat diatur dari menu Admin → Pengaturan

---

## 🔄 Alur Status Booking

```
DRAFT → PENDING_CONFIRMATION → (Review Owner/Provider)
      → APPROVED → PAID → IN_PROGRESS → COMPLETED

Jalur alternatif:
PENDING_CONFIRMATION → REJECTED → CANCELLED
Status lain dapat berakhir CANCELLED sesuai aturan transisi
```

---

## 💻 Tech Stack

| Komponen | Teknologi |
| --- | --- |
| Backend | PHP 8.2+, Laravel 11 |
| Frontend | Blade templating, Tailwind CSS 3, Vite 6, Axios |
| Database | MySQL / SQLite |
| Session & Cache | Database driver |
| AI Chatbot | Groq API (opsional, dikonfigurasi via menu Admin) |
| Testing | PHPUnit 11 |

---

## 📦 Instalasi

### Prasyarat
- PHP ≥ 8.2 (dengan ekstensi yang dibutuhkan Laravel)
- Composer
- Node.js & NPM
- MySQL (atau SQLite untuk development cepat)

### Langkah Instalasi

1. **Clone repository**
   ```bash
   git clone <url-repository>
   cd cohub-ia-itb
   ```

2. **Install dependency PHP**
   ```bash
   composer install
   ```

3. **Install dependency JavaScript**
   ```bash
   npm install
   ```

4. **Konfigurasi environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan `.env` — terutama konfigurasi database (`DB_*`).

5. **Jalankan migrasi database**
   ```bash
   php artisan migrate
   ```

6. **(Opsional) Isi data demo**
   ```bash
   php artisan db:seed
   ```
   > Seeder mengisi kategori, properti, jasa, booking, dan riwayat aktivitas contoh. Akses login tidak diinformasikan di dokumen ini — hubungi pengelola/admin proyek untuk kredensial akun.

7. **Build aset frontend**
   ```bash
   npm run build        # produksi
   # atau
   npm run dev          # development (hot reload)
   ```

8. **Jalankan server**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses di `http://localhost:8000`.

### Menjalankan Semua Secara Paralel (Dev)

```bash
composer dev
```
Perintah ini menjalankan server, queue, log viewer (Pail), dan Vite bersamaan.

---

## 🧪 Testing

```bash
php artisan test
# atau
./vendor/bin/phpunit
```

---

## 📁 Struktur Direktori (Ringkas)

```
cohub-ia-itb/
├── app/
│   ├── Enums/              # UserRole, BookingStatus, PropertyStatus, dll.
│   ├── Http/Controllers/   # Admin, Owner, Provider, Customer, Public, Auth
│   ├── Models/             # User, Property, Service, Booking, Payment, dll.
│   ├── Notifications/      # Notifikasi email & database
│   ├── Services/           # BookingService, GroqChatService, ImageService
│   └── Support/            # Helper functions
├── database/
│   ├── migrations/         # Skema database
│   └── seeders/            # Data demo
├── resources/
│   ├── views/              # Blade templates (admin, owner, provider, customer, public)
│   └── knowledge/          # Knowledge base untuk AI chatbot
├── routes/
│   └── web.php             # Definisi seluruh route aplikasi
└── config/                 # Konfigurasi aplikasi
```

---

## 🔐 Role & Hak Akses

| Role | Fungsi | Dashboard |
| --- | --- | --- |
| Customer/Penyewa | Mencari & menyewa properti/jasa | `/customer/dashboard` |
| Pemilik Properti | Menjual/menyewakan aset properti | `/owner/dashboard` |
| Penyedia Jasa | Menawarkan jasa profesional | `/provider/dashboard` |
| Administrator | Verifikasi, moderasi, user & transaksi | `/admin/dashboard` |

Setelah login/register, sistem otomatis mengarahkan pengguna ke dashboard sesuai role-nya.

---

## 📄 Lisensi

Proyek ini menggunakan [Laravel framework](https://laravel.com) yang dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

---

## 📞 Kontak

Untuk pertanyaan, kendala akses akun, atau kolaborasi, silakan hubungi pengelola proyek **CoHub IA ITB Jatim** atau gunakan fitur **Chat Admin** pada widget chat di situs.
# Cohub-IA-ITB
