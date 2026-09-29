<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PaymentStatus;
use App\Enums\PricePeriod;
use App\Enums\PriceUnit;
use App\Enums\PropertyStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\CustomerProfile;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyPrice;
use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\ServiceMedia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeder data demo CoHub IA ITB Jatim.
 *
 * Akun demo (password semua: password):
 * - superadmin@cohub.id    (Super Admin — bisa hapus transaksi)
 * - admin@cohub.id        (Admin)
 * - owner1@cohub.id       (Pemilik Properti - Surabaya)
 * - owner2@cohub.id       (Pemilik Properti - Malang)
 * - provider1@cohub.id    (Penyedia Jasa - Fotografi & Event)
 * - provider2@cohub.id    (Penyedia Jasa - IT & Rental)
 * - customer@cohub.id     (Customer utama)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Seeding data demo CoHub IA ITB Jatim...');

        $this->seedCategories();
        $users = $this->seedUsers();
        $properties = $this->seedProperties($users['owners']);
        $services = $this->seedServices($users['providers']);
        $this->seedBookings($users['customers'], $properties, $services, $users);

        // Data tambahan supaya pagination di semua menu admin kelihatan
        $this->seedExtraDemoData($users);

        // Riwayat aktivitas demo supaya halaman History Log langsung terisi
        $this->seedActivityLogs($users);

        $this->command?->info('Seeding selesai! Login: superadmin@cohub.id / admin@cohub.id / owner1@cohub.id / provider1@cohub.id / customer@cohub.id (password: password)');
    }

    /* ------------------------------------------------------------------ */

    private function seedCategories(): void
    {
        $properties = [
            ['name' => 'Ruko', 'slug' => 'ruko'],
            ['name' => 'Gudang', 'slug' => 'gudang'],
            ['name' => 'Kantor', 'slug' => 'kantor'],
            ['name' => 'Rumah', 'slug' => 'rumah'],
            ['name' => 'Villa', 'slug' => 'villa'],
            ['name' => 'Gedung', 'slug' => 'gedung'],
        ];

        $services = [
            ['name' => 'Fotografi & Video', 'slug' => 'fotografi-video'],
            ['name' => 'Catering', 'slug' => 'catering'],
            ['name' => 'Sound System', 'slug' => 'sound-system'],
            ['name' => 'IT & Website', 'slug' => 'it-website'],
            ['name' => 'Rental Tools', 'slug' => 'rental-tools'],
            ['name' => 'Desain Interior', 'slug' => 'desain-interior'],
        ];

        foreach ($properties as $c) {
            Category::updateOrCreate(['slug' => $c['slug']], $c + ['type' => 'property']);
        }
        foreach ($services as $c) {
            Category::updateOrCreate(['slug' => $c['slug']], $c + ['type' => 'service']);
        }
    }

    private function seedUsers(): array
    {
        // Super Admin (akses penuh, termasuk hapus transaksi)
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@cohub.id'],
            [
                'name' => 'Super Admin CoHub',
                'password' => 'password',
                'role' => UserRole::SuperAdmin,
                'whatsapp' => '628111000000',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@cohub.id'],
            [
                'name' => 'Admin CoHub',
                'password' => 'password',
                'role' => UserRole::Admin,
                'whatsapp' => '628111000001',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Pemilik properti
        $ownerData = [
            ['email' => 'owner1@cohub.id', 'name' => 'Budi Santoso', 'whatsapp' => '628111000002'],
            ['email' => 'owner2@cohub.id', 'name' => 'Sari Wijaya', 'whatsapp' => '628111000003'],
        ];
        $owners = [];
        foreach ($ownerData as $i => $d) {
            $owners[] = User::updateOrCreate(
                ['email' => $d['email']],
                $d + [
                    'password' => 'password',
                    'role' => UserRole::Owner,
                    'is_active' => true,
                    'email_verified_at' => now()->subDays(90 - $i),
                ]
            );
        }

        // Penyedia jasa
        $providerData = [
            ['email' => 'provider1@cohub.id', 'name' => 'Andi Pratama (Fotografer)', 'whatsapp' => '628111000004'],
            ['email' => 'provider2@cohub.id', 'name' => 'TechRent Solutions', 'whatsapp' => '628111000005'],
        ];
        $providers = [];
        foreach ($providerData as $i => $d) {
            $providers[] = User::updateOrCreate(
                ['email' => $d['email']],
                $d + [
                    'password' => 'password',
                    'role' => UserRole::Provider,
                    'is_active' => true,
                    'email_verified_at' => now()->subDays(80 - $i),
                ]
            );
        }

        // Customer
        $customerData = [
            ['email' => 'customer@cohub.id', 'name' => 'Dewi Lestari', 'whatsapp' => '628111000006'],
            ['email' => 'customer2@cohub.id', 'name' => 'Rian Hidayat', 'whatsapp' => '628111000007'],
            ['email' => 'customer3@cohub.id', 'name' => 'Maya Anggraini', 'whatsapp' => '628111000008'],
        ];
        $customers = [];
        foreach ($customerData as $i => $d) {
            $customers[] = User::updateOrCreate(
                ['email' => $d['email']],
                $d + [
                    'password' => 'password',
                    'role' => UserRole::Customer,
                    'is_active' => true,
                    'email_verified_at' => now()->subDays(60 - $i * 5),
                ]
            );
        }

        // Profil alamat customer
        $profiles = [
            ['provinsi' => 'Jawa Timur', 'kota_kabupaten' => 'Surabaya', 'kecamatan' => 'Gubeng', 'kelurahan' => 'Airlangga', 'kode_pos' => '60286', 'alamat' => 'Jl. Airlangga No. 10, dekat kampus ITS'],
            ['provinsi' => 'Jawa Timur', 'kota_kabupaten' => 'Malang', 'kecamatan' => 'Lowokwaru', 'kelurahan' => 'Tlogomas', 'kode_pos' => '65144', 'alamat' => 'Jl. Merbabu No. 24'],
            ['provinsi' => 'Jawa Timur', 'kota_kabupaten' => 'Sidoarjo', 'kecamatan' => 'Buduran', 'kelurahan' => 'Buduran', 'kode_pos' => '61252', 'alamat' => 'Perum Griya Asri Blok C2'],
        ];
        foreach ($customers as $i => $c) {
            CustomerProfile::updateOrCreate(['user_id' => $c->id], $profiles[$i]);
        }

        return compact('superAdmin', 'admin', 'owners', 'providers', 'customers');
    }

    private function seedProperties(array $owners): array
    {
        $catRuko = Category::where('slug', 'ruko')->first();
        $catKantor = Category::where('slug', 'kantor')->first();
        $catGudang = Category::where('slug', 'gudang')->first();
        $catVilla = Category::where('slug', 'villa')->first();
        $catGedung = Category::where('slug', 'gedung')->first();

        $data = [
            // [ownerIdx, category, name, desc, land, building, kota, kecamatan, address, status, verification, prices[daily,monthly,yearly]]
            [0, $catRuko, 'Ruko Modern Darmo Permai', 'Ruko 3 lantai strategis di kawasan bisnis Darmo Permai. Cocok untuk kantor, klinik, atau kafe. Parkir luas, dekat pintu tolak ARPSS.', 120, 240, 'Surabaya', 'Wonokromo', 'Jl. Darmo Permai III No. 45', 'available', 'approved', [null, 25000000, 250000000]],
            [0, $catKantor, 'Ruang Kantor Co-Working Gedung BRI', 'Lantai 8 gedung BRI Jalan Basuki Rahmat. Full furnished, meeting room, pantry, lift & security 24 jam.', 200, 450, 'Surabaya', 'Genteng', 'Jl. Basuki Rahmat No. 88, Lt. 8', 'available', 'approved', [750000, 12000000, null]],
            [0, $catGudang, 'Gudang Logistik Rungkut', 'Gudang 900 m2 dengan loading dock 3 unit, dirgantara 8 meter. Listrik 33.000 VA. Lokasi 5 menit dari Pelabuhan Tanjung Perak.', 1200, 900, 'Surabaya', 'Karang Pilang', 'Jl. Rungkut Industri Raya No. 12', 'available', 'approved', [null, 45000000, 480000000]],
            [0, $catGedung, 'Gedung Serbaguna IA ITB Jatim', 'Gedung serbaguna kapasitas 500 orang, cocok untuk seminar, reunion, atau pernikahan. Tersedia AC, panggung, dan area parkir 50 mobil.', 800, 1200, 'Surabaya', 'Sukolilo', 'Jl. Keputih Tegal Timur No. 1', 'maintenance', 'approved', [5000000, null, null]],
            [1, $catVilla, 'Villa Batu Pemandangan Alam', 'Vila 4 kamar tidur dengan pemandangan pegunungan Batu. Kolam renang pribadi, dapur lengkap, kapasitas 12 tamu.', 400, 320, 'Kota Batu', 'Bumiaji', 'Jl. Panderman Raya No. 7', 'available', 'approved', [1500000, 28000000, null]],
            [1, $catRuko, 'Ruko Landungsari Indah', 'Ruko 2 lantai area Malang dekat pintu tol. Cocok usaha F&B atau retail mahasiswa.', 84, 140, 'Malang', 'Lowokwaru', 'Jl. Landungsari No. 21', 'rented', 'approved', [null, 12000000, null]],
            [1, $catKantor, 'Kantor Kecil Ijen Suites', 'Kantor privat 60 m2 cocok untuk startup. Termasuk listrik, internet fiber, dan resepsionis bersama.', 100, 60, 'Malang', 'Klojen', 'Jl. Ijen Nirwana Raya Blok A-9', 'available', 'approved', [350000, 6500000, 60000000]],
            [1, $catGudang, 'Gudang Singosari (Pending Verifikasi)', 'Gudang industri 600 m2 di kawasan Singosari, satu pintu keluar masuk truk kontainer 20 kaki.', 900, 600, 'Malang', 'Singosari', 'Jl. Raya Singosari KM 12', 'available', 'pending', [null, 30000000, null]],
        ];

        $properties = [];
        foreach ($data as $i => $d) {
            [$ownerIdx, $cat, $name, $desc, $land, $building, $kota, $kec, $addr, $status, $verif, $prices] = $d;

            $property = Property::updateOrCreate(
                ['name' => $name],
                [
                    'user_id' => $owners[$ownerIdx]->id,
                    'category_id' => $cat->id,
                    'description' => $desc,
                    'land_size' => $land,
                    'building_size' => $building,
                    'provinsi' => 'Jawa Timur',
                    'kota_kabupaten' => $kota,
                    'kecamatan' => $kec,
                    'address' => $addr,
                    'status' => PropertyStatus::from($status),
                    'verification' => VerificationStatus::from($verif),
                ]
            );

            foreach ([PricePeriod::Daily, PricePeriod::Monthly, PricePeriod::Yearly] as $j => $period) {
                if ($prices[$j] !== null) {
                    PropertyPrice::updateOrCreate(
                        ['property_id' => $property->id, 'period' => $period->value],
                        ['price' => $prices[$j]]
                    );
                }
            }

            $properties[] = $property;
            $this->attachPlaceholder(
                model: $property,
                mediaClass: \App\Models\PropertyMedia::class,
                foreignKey: 'property_id',
                icon: $cat->slug === 'villa' ? '🏔' : ($cat->slug === 'gudang' ? '🏭' : ($cat->slug === 'gedung' ? '🏛' : ($cat->slug === 'kantor' ? '🏢' : ($cat->slug === 'rumah' ? '🏠' : '🏬')))),
                colors: $this->palette[$ownerIdx % count($this->palette)],
            );
        }

        return $properties;
    }

    private function seedServices(array $providers): array
    {
        $catFoto = Category::where('slug', 'fotografi-video')->first();
        $catCatering = Category::where('slug', 'catering')->first();
        $catIT = Category::where('slug', 'it-website')->first();
        $catRental = Category::where('slug', 'rental-tools')->first();
        $catSound = Category::where('slug', 'sound-system')->first();

        $data = [
            // [providerIdx, category, name, desc, startingPrice, unit, status, verification]
            [0, $catFoto, 'Jasa Fotografi Event & Reuni', 'Tim fotografer profesional berpengalaman 10 tahun meliputi reuni akbar, seminar, dan wisuda. Termasuk 2 fotografer + 1 videografer, file hasil edit 300+ foto.', 3500000, PriceUnit::PerProject->value, 'available', 'approved'],
            [0, $catFoto, 'Foto Produk & Kuliner', 'Sesi foto produk untuk UMKM. Termasuk props, lighting studio portable, 20 foto hasil edit siap marketplace.', 1250000, PriceUnit::PerProject->value, 'available', 'approved'],
            [1, $catIT, 'Pembuatan Website Company Profile', 'Website responsif SEO-ready untuk bisnis Anda. Termasuk domain .com 1 tahun, hosting 1 tahun, dan pelatihan pengelolaan konten.', 7500000, PriceUnit::PerProject->value, 'available', 'approved'],
            [1, $catRental, 'Sewa Proyektor & Sound System Portable', 'Paket proyektor 4000 lumens + layar + speaker portable 2x15 inch. Termasuk operator dan kabel lengkap.', 450000, PriceUnit::PerDay->value, 'available', 'approved'],
            [1, $catRental, 'Sewa Genset Silent 10 kVA', 'Genset silent type 10 kVA, cocok untuk event outdoor. Termasuk BBM 8 jam dan teknisi.', 850000, PriceUnit::PerDay->value, 'available', 'approved'],
            [1, $catIT, 'Instalasi CCTV & Jaringan Kantor', 'Instalasi CCTV IP camera sampai 8 titik + konfigurasi jaringan LAN kantor. Garansi instalasi 1 tahun.', 3500000, PriceUnit::PerProject->value, 'unavailable', 'approved'],
        ];

        $services = [];
        foreach ($data as $d) {
            [$pIdx, $cat, $name, $desc, $price, $unit, $status, $verif] = $d;

            $service = Service::updateOrCreate(
                ['name' => $name],
                [
                    'user_id' => $providers[$pIdx]->id,
                    'category_id' => $cat->id,
                    'description' => $desc,
                    'starting_price' => $price,
                    'price_unit' => $unit,
                    'status' => $status,
                    'verification' => VerificationStatus::from($verif),
                ]
            );

            // Availability Senin-Sabtu penuh (FR-SVC-04), jasa event hanya Senin-Sabtu 08-20
            $hours = str_contains($name, 'Event') ? ['08:00', '20:00'] : ['00:00', '23:59'];
            foreach (range(1, 6) as $day) {
                ServiceAvailability::updateOrCreate(
                    ['service_id' => $service->id, 'day_of_week' => $day],
                    ['start_time' => $hours[0], 'end_time' => $hours[1]]
                );
            }

            $services[] = $service;

            $this->attachPlaceholder(
                model: $service,
                mediaClass: ServiceMedia::class,
                foreignKey: 'service_id',
                icon: $cat->slug === 'fotografi-video' ? '📷' : ($cat->slug === 'catering' ? '🍽' : ($cat->slug === 'sound-system' ? '🔊' : ($cat->slug === 'it-website' ? '💻' : ($cat->slug === 'rental-tools' ? '🛠' : '🎨')))),
                colors: $this->palette[($pIdx + 2) % count($this->palette)],
            );
        }

        return $services;
    }

    private function seedBookings(array $customers, array $properties, array $services, array $users): void
    {
        // Hindari duplikat saat re-seed
        if (Booking::count() > 0) {
            return;
        }

        $seq = 1;
        $makeCode = function () use (&$seq) {
            return sprintf('BK-%s-%04d', now()->format('Ymd'), $seq++);
        };

        /* ---- Booking properti di berbagai status ---- */

        // 1. Draft sewa ruko bulanan (belum disubmit customer)
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Property->value,
            'customer_id' => $customers[0]->id,
            'property_id' => $properties[0]->id,
            'start_date' => now()->addDays(30)->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'price_period' => PricePeriod::Monthly->value,
            'unit_price' => 25000000,
            'total_price' => 25000000,
            'purpose' => 'Membuka kantor cabang usaha distribusi.',
            'status' => BookingStatus::Draft->value,
        ]);

        // 2. Pending confirmation - sewa kantor co-working bulanan
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Property->value,
            'customer_id' => $customers[0]->id,
            'property_id' => $properties[1]->id,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(40)->toDateString(),
            'price_period' => PricePeriod::Monthly->value,
            'unit_price' => 12000000,
            'total_price' => 12000000,
            'purpose' => 'Ruang kerja tim startup 6 orang.',
            'status' => BookingStatus::PendingConfirmation->value,
            'submitted_at' => now()->subDays(1),
        ]);
        $this->history($booking, null, BookingStatus::Draft);
        $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $users['customers'][0], 'Menunggu konfirmasi pemilik properti.');

        // 3. Approved + payment unpaid - sewa villa Batu weekend
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Property->value,
            'customer_id' => $customers[1]->id,
            'property_id' => $properties[4]->id,
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(16)->toDateString(),
            'price_period' => PricePeriod::Daily->value,
            'unit_price' => 1500000,
            'total_price' => 3000000,
            'purpose' => 'Liburan keluarga akhir pekan.',
            'status' => BookingStatus::Approved->value,
            'submitted_at' => now()->subDays(3),
            'approved_at' => now()->subDays(2),
        ]);
        $this->history($booking, null, BookingStatus::Draft);
        $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $users['customers'][1]);
        $this->history($booking, BookingStatus::PendingConfirmation, BookingStatus::Approved, $users['owners'][1], 'Silakan lanjutkan pembayaran.');
        Payment::create([
            'booking_id' => $booking->id,
            'invoice_no' => 'INV-' . $booking->code,
            'amount' => $booking->total_price,
            'method' => 'transfer',
            'status' => PaymentStatus::Unpaid->value,
        ]);

        // 4. Paid & in progress - sewa gudang 3 bulanan
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Property->value,
            'customer_id' => $customers[2]->id,
            'property_id' => $properties[2]->id,
            'start_date' => now()->subDays(15)->toDateString(),
            'end_date' => now()->subMonths(3)->addDays(15)->toDateString(),
            'price_period' => PricePeriod::Monthly->value,
            'unit_price' => 45000000,
            'total_price' => 135000000,
            'purpose' => 'Penyimpanan stok barang ekspor.',
            'status' => BookingStatus::InProgress->value,
            'submitted_at' => now()->subDays(25),
            'approved_at' => now()->subDays(24),
            'paid_at' => now()->subDays(23),
        ]);
        $this->history($booking, null, BookingStatus::Draft);
        $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $users['customers'][2]);
        $this->history($booking, BookingStatus::PendingConfirmation, BookingStatus::Approved, $users['owners'][0]);
        $this->history($booking, BookingStatus::Approved, BookingStatus::Paid, $users['customers'][2], 'Pembayaran transfer bank terverifikasi.');
        $this->history($booking, BookingStatus::Paid, BookingStatus::InProgress, $users['owners'][0], 'Masa sewa dimulai.');
        Payment::create([
            'booking_id' => $booking->id,
            'invoice_no' => 'INV-' . $booking->code,
            'amount' => $booking->total_price,
            'method' => 'transfer',
            'status' => PaymentStatus::Paid->value,
            'paid_at' => now()->subDays(23),
        ]);

        // 5. Completed - sewa kantor Malang 1 tahun (untuk laporan admin)
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Property->value,
            'customer_id' => $customers[1]->id,
            'property_id' => $properties[6]->id,
            'start_date' => now()->subMonths(6)->toDateString(),
            'end_date' => now()->toDateString(),
            'price_period' => PricePeriod::Yearly->value,
            'unit_price' => 60000000,
            'total_price' => 60000000,
            'purpose' => 'Kantor startup teknologi pertanian.',
            'status' => BookingStatus::Completed->value,
            'submitted_at' => now()->subMonths(6)->subDays(5),
            'approved_at' => now()->subMonths(6)->subDays(4),
            'paid_at' => now()->subMonths(6)->subDays(3),
            'completed_at' => now()->subDays(1),
        ]);
        $this->history($booking, null, BookingStatus::Draft);
        $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $users['customers'][1]);
        $this->history($booking, BookingStatus::PendingConfirmation, BookingStatus::Approved, $users['owners'][1]);
        $this->history($booking, BookingStatus::Approved, BookingStatus::Paid, $users['customers'][1]);
        $this->history($booking, BookingStatus::Paid, BookingStatus::InProgress, $users['owners'][1]);
        $this->history($booking, BookingStatus::InProgress, BookingStatus::Completed, $users['owners'][1], 'Sewa selesai, barang sudah dipindahkan.');
        Payment::create([
            'booking_id' => $booking->id,
            'invoice_no' => 'INV-' . $booking->code,
            'amount' => $booking->total_price,
            'method' => 'transfer',
            'status' => PaymentStatus::Paid->value,
            'paid_at' => now()->subMonths(6)->subDays(3),
        ]);

        // 6. Rejected
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Property->value,
            'customer_id' => $customers[2]->id,
            'property_id' => $properties[0]->id,
            'start_date' => now()->addDays(20)->toDateString(),
            'end_date' => now()->addDays(50)->toDateString(),
            'price_period' => PricePeriod::Monthly->value,
            'unit_price' => 25000000,
            'total_price' => 25000000,
            'purpose' => 'Kantor baru divisi pemasaran.',
            'status' => BookingStatus::Rejected->value,
            'submitted_at' => now()->subDays(5),
        ]);
        $this->history($booking, null, BookingStatus::Draft);
        $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $users['customers'][2]);
        $this->history($booking, BookingStatus::PendingConfirmation, BookingStatus::Rejected, $users['owners'][0], 'Sudah ada peminjam lain dengan jadwal lebih awal.');

        /* ---- Booking jasa ---- */

        // 7. Pending - fotografi reuni
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Service->value,
            'customer_id' => $customers[0]->id,
            'service_id' => $services[0]->id,
            'start_date' => now()->addDays(7)->toDateString(),
            'schedule_time' => '08:00',
            'quantity' => 1,
            'unit_price' => 3500000,
            'total_price' => 3500000,
            'note' => 'Reuni akabar angkatan 2005 di gedung serbaguna. Butuh candid + group photo.',
            'status' => BookingStatus::PendingConfirmation->value,
            'submitted_at' => now()->subHours(6),
        ]);
        $this->history($booking, null, BookingStatus::Draft);
        $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $users['customers'][0]);

        // 8. Approved - rental proyektor
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Service->value,
            'customer_id' => $customers[1]->id,
            'service_id' => $services[3]->id,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'schedule_time' => '09:00',
            'quantity' => 2,
            'unit_price' => 450000,
            'total_price' => 900000,
            'note' => 'Untuk seminar teknologi di aula kampus. Perlu 2 unit.',
            'status' => BookingStatus::Approved->value,
            'submitted_at' => now()->subDays(2),
            'approved_at' => now()->subDays(1),
        ]);
        $this->history($booking, null, BookingStatus::Draft);
        $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $users['customers'][1]);
        $this->history($booking, BookingStatus::PendingConfirmation, BookingStatus::Approved, $users['providers'][1], 'Tersedia, silakan lakukan pembayaran.');
        Payment::create([
            'booking_id' => $booking->id,
            'invoice_no' => 'INV-' . $booking->code,
            'amount' => $booking->total_price,
            'method' => 'transfer',
            'status' => PaymentStatus::Unpaid->value,
        ]);

        // 9. In progress - pembuatan website
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Service->value,
            'customer_id' => $customers[2]->id,
            'service_id' => $services[2]->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'schedule_time' => '10:00',
            'quantity' => 1,
            'unit_price' => 7500000,
            'total_price' => 7500000,
            'note' => 'Website company profile untuk perusahaan furniture export.',
            'status' => BookingStatus::InProgress->value,
            'submitted_at' => now()->subDays(15),
            'approved_at' => now()->subDays(14),
            'paid_at' => now()->subDays(13),
        ]);
        $this->history($booking, null, BookingStatus::Draft);
        $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $users['customers'][2]);
        $this->history($booking, BookingStatus::PendingConfirmation, BookingStatus::Approved, $users['providers'][1]);
        $this->history($booking, BookingStatus::Approved, BookingStatus::Paid, $users['customers'][2]);
        $this->history($booking, BookingStatus::Paid, BookingStatus::InProgress, $users['providers'][1], 'Pengerjaan dimulai, estimasi 3 minggu.');
        Payment::create([
            'booking_id' => $booking->id,
            'invoice_no' => 'INV-' . $booking->code,
            'amount' => $booking->total_price,
            'method' => 'transfer',
            'status' => PaymentStatus::Paid->value,
            'paid_at' => now()->subDays(13),
        ]);

        // 10. Completed - foto produk
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Service->value,
            'customer_id' => $customers[0]->id,
            'service_id' => $services[1]->id,
            'start_date' => now()->subDays(20)->toDateString(),
            'schedule_time' => '13:00',
            'quantity' => 2,
            'unit_price' => 1250000,
            'total_price' => 2500000,
            'note' => 'Foto 2 paket produk kuliner untuk katalog online.',
            'status' => BookingStatus::Completed->value,
            'submitted_at' => now()->subDays(25),
            'approved_at' => now()->subDays(24),
            'paid_at' => now()->subDays(24),
            'completed_at' => now()->subDays(18),
        ]);
        $this->history($booking, null, BookingStatus::Draft);
        $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $users['customers'][0]);
        $this->history($booking, BookingStatus::PendingConfirmation, BookingStatus::Approved, $users['providers'][0]);
        $this->history($booking, BookingStatus::Approved, BookingStatus::Paid, $users['customers'][0]);
        $this->history($booking, BookingStatus::Paid, BookingStatus::InProgress, $users['providers'][0]);
        $this->history($booking, BookingStatus::InProgress, BookingStatus::Completed, $users['providers'][0], 'File akhir sudah dikirim via Google Drive.');
        Payment::create([
            'booking_id' => $booking->id,
            'invoice_no' => 'INV-' . $booking->code,
            'amount' => $booking->total_price,
            'method' => 'transfer',
            'status' => PaymentStatus::Paid->value,
            'paid_at' => now()->subDays(24),
        ]);

        // 11. Cancelled oleh customer
        $booking = Booking::create([
            'code' => $makeCode(),
            'type' => BookingType::Service->value,
            'customer_id' => $customers[1]->id,
            'service_id' => $services[4]->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'schedule_time' => '08:00',
            'quantity' => 1,
            'unit_price' => 850000,
            'total_price' => 850000,
            'status' => BookingStatus::Cancelled->value,
            'submitted_at' => now()->subDays(4),
        ]);
        $this->history($booking, null, BookingStatus::Draft);
        $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $users['customers'][1]);
        $this->history($booking, BookingStatus::PendingConfirmation, BookingStatus::Cancelled, $users['customers'][1], 'Event ditunda oleh panitia.');
    }

    private function history(Booking $booking, ?BookingStatus $from, BookingStatus $to, ?User $actor = null, ?string $note = null): void
    {
        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'actor_id' => $actor?->id,
            'note' => $note,
            'created_at' => $booking->created_at?->addMinutes(rand(1, 240)),
        ]);
    }

    /* ==================================================================
       DATA TAMBAHAN — supaya pagination di semua menu admin kelihatan
       (pengguna > 10, properti & jasa > 6, transaksi > 10)
       ================================================================== */

    private function seedExtraDemoData(array $users): void
    {
        $extra = $this->seedExtraUsers();

        $owners = array_merge($users['owners'], $extra['owners']);
        $providers = array_merge($users['providers'], $extra['providers']);
        $customers = array_merge($users['customers'], $extra['customers']);

        $properties = array_merge(
            $this->seedProperties($owners),   // idempotent: properti lama di-skip, baru dibuat
            $this->seedExtraProperties($owners),
        );
        $services = array_merge(
            $this->seedServices($providers),
            $this->seedExtraServices($providers),
        );

        $this->seedExtraBookings($customers, $properties, $services);
    }

    /** Pengguna tambahan: 8 customer, 1 pemilik, 1 penyedia. */
    private function seedExtraUsers(): array
    {
        $customerData = [
            ['customer4@cohub.id', 'Agus Salim', '628111000009'],
            ['customer5@cohub.id', 'Putri Ramadhani', '628111000010'],
            ['customer6@cohub.id', 'Bagus Prasetyo', '628111000011'],
            ['customer7@cohub.id', 'Nurul Aini', '628111000012'],
            ['customer8@cohub.id', 'Fajar Nugroho', '628111000013'],
            ['customer9@cohub.id', 'Lestari Wulandari', '628111000014'],
            ['customer10@cohub.id', 'Dimas Saputra', '628111000015'],
            ['customer11@cohub.id', 'Intan Permata', '628111000016'],
        ];
        $customers = [];
        foreach ($customerData as $i => $d) {
            $customers[] = User::updateOrCreate(
                ['email' => $d[0]],
                [
                    'name' => $d[1],
                    'password' => 'password',
                    'role' => UserRole::Customer,
                    'whatsapp' => $d[2],
                    'is_active' => true,
                    'email_verified_at' => now()->subDays(50 - $i),
                ]
            );

            CustomerProfile::updateOrCreate(
                ['user_id' => $customers[$i]->id],
                ['provinsi' => 'Jawa Timur', 'kota_kabupaten' => 'Surabaya', 'alamat' => 'Alamat demo customer '.($i + 4)],
            );
        }

        $owners = [];
        $owners[] = User::updateOrCreate(
            ['email' => 'owner3@cohub.id'],
            [
                'name' => 'Hendra Gunawan',
                'password' => 'password',
                'role' => UserRole::Owner,
                'whatsapp' => '628111000017',
                'is_active' => true,
                'email_verified_at' => now()->subDays(70),
            ]
        );

        $providers = [];
        $providers[] = User::updateOrCreate(
            ['email' => 'provider3@cohub.id'],
            [
                'name' => 'Rizky Creative Studio',
                'password' => 'password',
                'role' => UserRole::Provider,
                'whatsapp' => '628111000018',
                'is_active' => true,
                'email_verified_at' => now()->subDays(65),
            ]
        );

        return compact('customers', 'owners', 'providers');
    }

    /** Properti tambahan (verifikasi campur: approved/pending/rejected). */
    private function seedExtraProperties(array $owners): array
    {
        $cat = fn (string $slug) => Category::where('slug', $slug)->first();

        // [ownerIdx, catSlug, name, desc, land, building, kota, kecamatan, address, status, verification, prices[daily, monthly, yearly]]
        $data = [
            [0, 'ruko', 'Ruko Boulevard Rungkut Asri', 'Ruko 2 lantai di boulevard Rungkut Asri, lokasi strategis dekat universitas dan pemukiman padat.', 90, 160, 'Surabaya', 'Rungkut', 'Jl. Boulevard Rungkut Asri No. 12', 'available', 'approved', [null, 15000000, null]],
            [1, 'gudang', 'Gudang Industri Sidoarjo Megah', 'Gudang 1.500 m2 di kawasan industri Sidoarjo, truk kontainer 40 kaki bisa masuk.', 1500, 1500, 'Sidoarjo', 'Buduran', 'Jl. Raya Gedangan No. 88', 'available', 'pending', [null, 55000000, 600000000]],
            [2, 'kantor', 'Kantor Virtual Gateway Surabaya', 'Kantor virtual + meeting room bulanan, cocok untuk startup remote team. Termasuk alamat bisnis premium.', 50, 120, 'Surabaya', 'Genteng', 'Jl. HR Muhammad No. 45', 'available', 'approved', [null, 3500000, 36000000]],
            [1, 'villa', 'Villa Orchard Batu Selecta', 'Villa pegunungan 5 kamar dengan kebun apel privat, cocok untuk retret keluarga & company outing.', 600, 450, 'Kota Batu', 'Bumiaji', 'Jl. Selecta Raya No. 3', 'available', 'approved', [1750000, 32000000, null]],
            [2, 'rumah', 'Rumah Modern Griya Shanta', 'Rumah 3 kamar minimalis di cluster Griya Shanta, lingkungan asri & keamanan 24 jam.', 120, 90, 'Malang', 'Lowokwaru', 'Jl. Griya Shanta Selatan Blok D-9', 'rented', 'pending', [null, 9000000, null]],
            [0, 'gedung', 'Gedung Aula Alumni ITS', 'Aula ber-AC kapasitas 300 orang dengan parkir luas, cocok untuk seminar & workshop alumni.', 500, 800, 'Surabaya', 'Sukolilo', 'Jl. Teknik Kimia No. 1', 'available', 'approved', [4000000, null, null]],
            [0, 'ruko', 'Ruko Darmo Satelit', 'Ruko 3 lantai zona bisnis Darmo Satelit, cocok untuk klinik atau kantor cabang.', 110, 250, 'Surabaya', 'Wonokromo', 'Jl. Darmo Satelit No. 7', 'available', 'rejected', [null, 22000000, null]],
            [2, 'gudang', 'Gudang Manyar Karya', 'Gudang 700 m2 dekat tol Manyar, listrik 22.000 VA, satu pintu dok tinggi.', 700, 700, 'Surabaya', 'Sukolilo', 'Jl. Manyar Karya No. 21', 'available', 'approved', [null, 38000000, null]],
            [1, 'kantor', 'Kantor Coworking Malang Creative Center', 'Lantai coworking kreatif di jantung kota Malang, termasuk studio foto mini & ruang rapat.', 300, 280, 'Malang', 'Klojen', 'Jl. Merdeka Barat No. 5', 'available', 'pending', [150000, 4500000, null]],
            [2, 'villa', 'Villa Tretes Sejuk', 'Villa 3 kamar di kaki gunung Tretes, udara sejuk dengan taman luas untuk gathering.', 350, 220, 'Pasuruan', 'Prigen', 'Jl. Raya Tretes KM 4', 'available', 'approved', [1200000, 22000000, null]],
        ];

        $properties = [];
        foreach ($data as $i => $d) {
            [$ownerIdx, $slug, $name, $desc, $land, $building, $kota, $kec, $addr, $status, $verif, $prices] = $d;
            $category = $cat($slug);
            if (! $category) {
                continue;
            }

            $property = Property::updateOrCreate(
                ['name' => $name],
                [
                    'user_id' => $owners[$ownerIdx % count($owners)]->id,
                    'category_id' => $category->id,
                    'description' => $desc,
                    'land_size' => $land,
                    'building_size' => $building,
                    'provinsi' => 'Jawa Timur',
                    'kota_kabupaten' => $kota,
                    'kecamatan' => $kec,
                    'address' => $addr,
                    'status' => PropertyStatus::from($status),
                    'verification' => VerificationStatus::from($verif),
                ]
            );

            foreach ([PricePeriod::Daily, PricePeriod::Monthly, PricePeriod::Yearly] as $j => $period) {
                if ($prices[$j] !== null) {
                    PropertyPrice::updateOrCreate(
                        ['property_id' => $property->id, 'period' => $period->value],
                        ['price' => $prices[$j]]
                    );
                }
            }

            $properties[] = $property;
            $this->attachPlaceholder(
                model: $property,
                mediaClass: \App\Models\PropertyMedia::class,
                foreignKey: 'property_id',
                icon: $slug === 'villa' ? '🏔' : ($slug === 'gudang' ? '🏭' : ($slug === 'gedung' ? '🏛' : ($slug === 'kantor' ? '🏢' : ($slug === 'rumah' ? '🏠' : '🏬')))),
                colors: $this->palette[($i + 1) % count($this->palette)],
            );
        }

        return $properties;
    }

    /** Jasa tambahan (verifikasi campur: approved/pending/rejected). */
    private function seedExtraServices(array $providers): array
    {
        $cat = fn (string $slug) => Category::where('slug', $slug)->first();

        // [providerIdx, catSlug, name, desc, price, unit, status, verification]
        $data = [
            [0, 'fotografi-video', 'Live Streaming & Dokumentasi Event', 'Tim live streaming multi-kamera untuk seminar, product launching, dan pernikahan. Termasuk platform streaming & rekaman akhir.', 5500000, PriceUnit::PerProject->value, 'available', 'approved'],
            [2, 'catering', 'Katering Prasmanan 100 Pax', 'Paket prasmanan 100 pax nasi box premium, 5 menu utama, termasuk tenda buffet & waiter.', 12500000, PriceUnit::PerProject->value, 'available', 'approved'],
            [2, 'sound-system', 'Sound System Akbar Outdoor', 'Paket outdoor 2 unit line array 15 inch, mixer digital, 4 mic wireless, dan 2 operator.', 7500000, PriceUnit::PerDay->value, 'available', 'pending'],
            [2, 'desain-interior', 'Desain Interior 3D & Rendering', 'Jasa desain interior lengkap dengan 3D rendering fotorealistis, working drawing, dan RAB furnitur.', 4500000, PriceUnit::PerProject->value, 'available', 'approved'],
            [2, 'rental-tools', 'Sewa Tenda & Panggung Event', 'Tenda Riko 4x6 m, panggung kayu 1x2 m modular, termasuk instalasi dan takedown.', 2500000, PriceUnit::PerDay->value, 'available', 'pending'],
            [0, 'fotografi-video', 'Fotografi Produk Fashion', 'Sesi foto fashion lookbook model termasuk makeup artist, 3 outfit, 15 foto edit premium.', 2000000, PriceUnit::PerProject->value, 'available', 'rejected'],
            [1, 'it-website', 'Maintenance Server & Cloud Bulanan', 'Managed server/VPS bulanan: monitoring 24/7, backup harian, patching keamanan, dan support prioritas.', 1500000, PriceUnit::PerUnit->value, 'available', 'approved'],
            [1, 'rental-tools', 'Sewa LED Screen Indoor', 'LED screen indoor P3 3x2 m termasuk processor, rigged stand, dan operator.', 3500000, PriceUnit::PerDay->value, 'available', 'pending'],
        ];

        $services = [];
        foreach ($data as $i => $d) {
            [$pIdx, $slug, $name, $desc, $price, $unit, $status, $verif] = $d;
            $category = $cat($slug);
            if (! $category) {
                continue;
            }

            $service = Service::updateOrCreate(
                ['name' => $name],
                [
                    'user_id' => $providers[$pIdx % count($providers)]->id,
                    'category_id' => $category->id,
                    'description' => $desc,
                    'starting_price' => $price,
                    'price_unit' => $unit,
                    'status' => $status,
                    'verification' => VerificationStatus::from($verif),
                ]
            );

            foreach (range(1, 5) as $day) {
                ServiceAvailability::updateOrCreate(
                    ['service_id' => $service->id, 'day_of_week' => $day],
                    ['start_time' => '08:00', 'end_time' => '20:00']
                );
            }

            $services[] = $service;

            $this->attachPlaceholder(
                model: $service,
                mediaClass: ServiceMedia::class,
                foreignKey: 'service_id',
                icon: $slug === 'fotografi-video' ? '📷' : ($slug === 'catering' ? '🍽' : ($slug === 'sound-system' ? '🔊' : ($slug === 'it-website' ? '💻' : ($slug === 'rental-tools' ? '🛠' : '🎨')))),
                colors: $this->palette[($i + 3) % count($this->palette)],
            );
        }

        return $services;
    }

    /** Transaksi tambahan tersebar 6 bulan terakhir (untuk grafik, laporan, & pagination). */
    private function seedExtraBookings(array $customers, array $properties, array $services): void
    {
        $approvedProperties = array_values(array_filter(
            $properties,
            fn ($p) => $p->verification === VerificationStatus::Approved
        ));
        $approvedServices = array_values(array_filter(
            $services,
            fn ($s) => $s->verification === VerificationStatus::Approved
        ));

        if (count($approvedProperties) < 2 || count($approvedServices) < 2) {
            return;
        }

        $statuses = [
            BookingStatus::Completed,
            BookingStatus::InProgress,
            BookingStatus::Paid,
            BookingStatus::Approved,
            BookingStatus::PendingConfirmation,
            BookingStatus::Rejected,
            BookingStatus::Cancelled,
            BookingStatus::Completed,
        ];

        $total = 24;
        for ($i = 0; $i < $total; $i++) {
            $customer = $customers[$i % count($customers)];
            $status = $statuses[$i % count($statuses)];
            $createdAt = now()->subDays(intval(160 - ($i * 156 / max(1, $total - 1))))->setTime(9 + ($i % 9), ($i * 13) % 60);
            $code = sprintf('BK-%s-%04d', $createdAt->format('Ymd'), 101 + $i);

            if (Booking::where('code', $code)->exists()) {
                continue;
            }

            $useProperty = $i % 2 === 0;
            $months = ($i % 3) + 1;
            $qty = ($i % 3) + 1;

            if ($useProperty) {
                $item = $approvedProperties[$i % count($approvedProperties)];
                $unit = (int) ($item->prices->firstWhere('period', PricePeriod::Monthly->value)?->price
                    ?? $item->prices->first()?->price
                    ?? 5000000);
                $attrs = [
                    'type' => BookingType::Property->value,
                    'property_id' => $item->id,
                    'start_date' => $createdAt->copy()->addDays(10)->toDateString(),
                    'end_date' => $createdAt->copy()->addDays(10)->addMonths($months)->toDateString(),
                    'price_period' => PricePeriod::Monthly->value,
                    'unit_price' => $unit,
                    'total_price' => $unit * $months,
                    'purpose' => 'Kebutuhan operasional bisnis (data demo).',
                ];
                $partner = $item->owner;
            } else {
                $item = $approvedServices[$i % count($approvedServices)];
                $unit = (int) $item->starting_price;
                $attrs = [
                    'type' => BookingType::Service->value,
                    'service_id' => $item->id,
                    'start_date' => $createdAt->copy()->addDays(7)->toDateString(),
                    'schedule_time' => '09:00',
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'total_price' => $unit * $qty,
                    'note' => 'Catatan kebutuhan dari customer (data demo).',
                ];
                $partner = $item->provider;
            }

            $timestamps = match ($status) {
                BookingStatus::PendingConfirmation => ['submitted_at' => $createdAt->copy()->addHours(2)],
                BookingStatus::Approved => ['submitted_at' => $createdAt->copy()->addHours(2), 'approved_at' => $createdAt->copy()->addHours(26)],
                BookingStatus::Rejected => ['submitted_at' => $createdAt->copy()->addHours(2)],
                BookingStatus::Cancelled => ['submitted_at' => $createdAt->copy()->addHours(2)],
                BookingStatus::Paid => ['submitted_at' => $createdAt->copy()->addHours(2), 'approved_at' => $createdAt->copy()->addHours(26), 'paid_at' => $createdAt->copy()->addHours(50)],
                BookingStatus::InProgress => ['submitted_at' => $createdAt->copy()->addHours(2), 'approved_at' => $createdAt->copy()->addHours(26), 'paid_at' => $createdAt->copy()->addHours(50)],
                BookingStatus::Completed => ['submitted_at' => $createdAt->copy()->addHours(2), 'approved_at' => $createdAt->copy()->addHours(26), 'paid_at' => $createdAt->copy()->addHours(50), 'completed_at' => $createdAt->copy()->addDays(20)],
                default => [],
            };

            $booking = Booking::create($attrs + [
                'code' => $code,
                'customer_id' => $customer->id,
                'status' => $status->value,
            ] + $timestamps);

            $booking->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

            // Riwayat status berurutan (audit trail)
            $this->history($booking, null, BookingStatus::Draft, null);
            $this->history($booking, BookingStatus::Draft, BookingStatus::PendingConfirmation, $customer);

            $chain = [BookingStatus::Approved, BookingStatus::Paid, BookingStatus::InProgress, BookingStatus::Completed];
            $reached = array_search($status, $chain, true);
            if ($reached !== false) {
                $prev = BookingStatus::PendingConfirmation;
                $actor = $partner;
                foreach (array_slice($chain, 0, $reached + 1) as $step) {
                    $this->history($booking, $prev, $step, $actor, $step === BookingStatus::Approved ? 'Disetujui, silakan lanjutkan pembayaran.' : null);
                    $prev = $step;
                    $actor = $step === BookingStatus::Completed ? $partner : $customer;
                }
            } elseif ($status === BookingStatus::Rejected) {
                $this->history($booking, BookingStatus::PendingConfirmation, BookingStatus::Rejected, $partner, 'Jadwal tidak tersedia untuk periode tersebut.');
            } elseif ($status === BookingStatus::Cancelled) {
                $this->history($booking, BookingStatus::PendingConfirmation, BookingStatus::Cancelled, $customer, 'Dibatalkan oleh customer.');
            }

            // Invoice / pembayaran
            if (in_array($status, [BookingStatus::Approved, BookingStatus::Paid, BookingStatus::InProgress, BookingStatus::Completed], true)) {
                Payment::firstOrCreate(
                    ['invoice_no' => 'INV-'.$code],
                    [
                        'booking_id' => $booking->id,
                        'amount' => $booking->total_price,
                        'method' => 'transfer',
                        'status' => in_array($status, [BookingStatus::Paid, BookingStatus::InProgress, BookingStatus::Completed], true)
                            ? PaymentStatus::Paid->value
                            : PaymentStatus::Unpaid->value,
                        'paid_at' => $timestamps['paid_at'] ?? null,
                    ]
                );
            }
        }
    }

    /** Riwayat aktivitas demo (idempoten: hanya jika tabel masih kosong). */
    private function seedActivityLogs(array $users): void
    {
        if (ActivityLog::count() > 0) {
            return;
        }

        $logs = [
            [$users['superAdmin'], 'login', 'auth', 'Super Admin CoHub (Super Administrator) berhasil masuk.', 90],
            [$users['admin'], 'login', 'auth', 'Admin CoHub (Administrator) berhasil masuk.', 85],
            [$users['owners'][0], 'create', 'properti', 'Properti baru "Ruko Modern Darmo Permai" didaftarkan & menunggu verifikasi.', 80],
            [$users['admin'], 'verify', 'properti', 'Properti "Ruko Modern Darmo Permai" disetujui & dipublikasikan.', 79],
            [$users['providers'][0], 'create', 'jasa', 'Jasa baru "Jasa Fotografi Event & Reuni" didaftarkan & menunggu verifikasi.', 75],
            [$users['admin'], 'verify', 'jasa', 'Jasa "Jasa Fotografi Event & Reuni" disetujui & dipublikasikan.', 74],
            [$users['customers'][0], 'create', 'transaksi', 'Booking sewa properti "Ruang Kantor Co-Working Gedung BRI" dibuat.', 70],
            [$users['customers'][0], 'submit', 'transaksi', 'Booking BK: Draft → Menunggu Konfirmasi oleh Dewi Lestari.', 69],
            [$users['owners'][0], 'approve', 'transaksi', 'Booking: Menunggu Konfirmasi → Disetujui oleh Budi Santoso.', 68],
            [$users['customers'][0], 'pay', 'transaksi', 'Booking: Disetujui → Dibayar oleh Dewi Lestari.', 67],
            [$users['admin'], 'settings', 'pengaturan', 'Pengaturan landing page disimpan (judul: "Find Your Perfect Space & Services").', 60],
            [$users['admin'], 'role', 'pengguna', 'Role "Rian Hidayat" diubah menjadi Customer / Penyewa.', 55],
            [$users['owners'][1], 'update', 'properti', 'Properti "Villa Batu Pemandangan Alam" diperbarui datanya.', 50],
            [$users['customers'][1], 'cancel', 'transaksi', 'Booking: Menunggu Konfirmasi → Dibatalkan oleh Rian Hidayat.', 45],
            [$users['providers'][1], 'logout', 'auth', 'TechRent Solutions keluar dari aplikasi.', 40],
            [$users['admin'], 'login', 'auth', 'Admin CoHub (Administrator) berhasil masuk.', 35],
        ];

        foreach ($logs as [$user, $action, $module, $description, $minutesAgo]) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Seeder Demo)',
                'created_at' => now()->subMinutes($minutesAgo),
                'updated_at' => now()->subMinutes($minutesAgo),
            ]);
        }
    }

    /* ---------------- Media placeholder SVG ---------------- */

    /** Palet warna gradien [atas, bawah] */
    private array $palette = [
        ['#0ea5e9', '#1e3a8a'], // biru
        ['#059669', '#065f46'], // hijau
        ['#f59e0b', '#b45309'], // amber
        ['#8b5cf6', '#5b21b6'], // ungu
    ];

    /**
     * Buat placeholder SVG ber-gradien sebagai thumbnail media item
     * (file nyata di disk public sehingga demo terlihat lengkap).
     */
    private function attachPlaceholder(object $model, string $mediaClass, string $foreignKey, string $icon, array $colors): void
    {
        if ($model->media()->count() > 0) {
            return;
        }

        $path = 'placeholders/' . Str::slug($model->name) . '-' . $model->id . '.svg';
        Storage::disk('public')->put($path, $this->placeholderSvg($model->name, $icon, $colors, $model->kota_kabupaten ?? 'Jasa CoHub'));

        $mediaClass::create([
            $foreignKey => $model->id,
            'path' => $path,
            'type' => 'image',
            'is_thumbnail' => true,
            'sort_order' => 0,
        ]);
    }

    private function placeholderSvg(string $title, string $icon, array $colors, string $subtitle): string
    {
        $title = htmlspecialchars(Str::limit($title, 32), ENT_QUOTES);
        $subtitle = htmlspecialchars(Str::limit($subtitle, 32), ENT_QUOTES);
        $short = explode(' ', $title)[0] . (explode(' ', $title)[1] ?? '');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="500" viewBox="0 0 800 500">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$colors[0]}"/>
      <stop offset="100%" stop-color="{$colors[1]}"/>
    </linearGradient>
  </defs>
  <rect width="800" height="500" fill="url(#g)"/>
  <circle cx="700" cy="60" r="140" fill="#ffffff" opacity="0.08"/>
  <circle cx="80" cy="440" r="180" fill="#ffffff" opacity="0.06"/>
  <text x="400" y="215" font-size="110" text-anchor="middle" fill="#ffffff" opacity="0.9">{$icon}</text>
  <text x="400" y="320" font-family="Helvetica, Arial, sans-serif" font-size="34" font-weight="bold" text-anchor="middle" fill="#ffffff">{$short}</text>
  <text x="400" y="365" font-family="Helvetica, Arial, sans-serif" font-size="22" text-anchor="middle" fill="#ffffff" opacity="0.75">{$subtitle}</text>
  <text x="400" y="470" font-family="Helvetica, Arial, sans-serif" font-size="16" letter-spacing="4" text-anchor="middle" fill="#ffffff" opacity="0.6">COHUB IA ITB JATIM</text>
</svg>
SVG;
    }
}
