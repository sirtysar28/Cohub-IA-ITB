<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Owner;
use App\Http\Controllers\Provider;
use App\Http\Controllers\Public as PublicControllers;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — CoHub IA ITB Jatim
| Struktur mengikuti "Workflow Login & Role" + "Struktur Menu Laravel" dokumen
|--------------------------------------------------------------------------
*/

/* ------------------------------ PUBLIC ------------------------------ */

Route::get('/', [PublicControllers\HomeController::class, 'index'])->name('home');
Route::get('/properties', [PublicControllers\PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property}', [PublicControllers\PropertyController::class, 'show'])->name('properties.show');
Route::get('/services', [PublicControllers\ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [PublicControllers\ServiceController::class, 'show'])->name('services.show');

/* ------------------------------- AUTH ------------------------------- */

Route::get('/captcha/refresh', [LoginController::class, 'refreshCaptcha'])->name('captcha.refresh');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/* ------------------------- REDIRECT NOTIFIKASI ------------------------ */

Route::get('/bookings/{booking}/redirect', [Customer\BookingController::class, 'redirect'])
    ->middleware('auth')->name('bookings.redirect');

Route::middleware('auth')->group(function () {
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');

    // Profil (semua role) + ganti password
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
});

/* --------------------------- WIDGET CHAT ------------------------------
 * Mode "ai"    → chatbot Groq menjawab otomatis (bahan: workflow CoHub)
 * Mode "admin" → masuk konsol Admin → Chat Masuk
 * --------------------------------------------------------------------- */

Route::middleware(['throttle:30,1'])->prefix('chat')->group(function () {
    Route::post('/open', [\App\Http\Controllers\ChatController::class, 'open'])->name('chat.open');
    Route::get('/{conversation}/messages', [\App\Http\Controllers\ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/{conversation}/messages', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
});

/* ------------------------------ CUSTOMER ------------------------------ */

Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [Customer\DashboardController::class, 'index'])->name('dashboard');

    // Modul Penyewa & Pencarian -> Booking
    Route::get('/bookings', [Customer\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [Customer\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/submit', [Customer\BookingController::class, 'submit'])->name('bookings.submit');
    Route::post('/bookings/{booking}/pay', [Customer\BookingController::class, 'pay'])->name('bookings.pay');
    Route::post('/bookings/{booking}/cancel', [Customer\BookingController::class, 'cancel'])->name('bookings.cancel');
});

// Form booking dibuka untuk customer (di luar prefix agar URL bersih)
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/properties/{property}/book', [Customer\BookingController::class, 'createProperty'])->name('customer.bookings.create-property');
    Route::post('/properties/{property}/book', [Customer\BookingController::class, 'storeProperty'])->name('customer.bookings.store-property');
    Route::get('/services/{service}/book', [Customer\BookingController::class, 'createService'])->name('customer.bookings.create-service');
    Route::post('/services/{service}/book', [Customer\BookingController::class, 'storeService'])->name('customer.bookings.store-service');
});

/* -------------------------- PEMILIK PROPERTI -------------------------- */

Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [Owner\DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Properti (Modul 1)
    Route::get('/properties', [Owner\PropertyController::class, 'index'])->name('properties.index');
    Route::get('/properties/create', [Owner\PropertyController::class, 'create'])->name('properties.create');
    Route::post('/properties', [Owner\PropertyController::class, 'store'])->name('properties.store');
    Route::get('/properties/{property}', [Owner\PropertyController::class, 'show'])->name('properties.show');
    Route::get('/properties/{property}/edit', [Owner\PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('/properties/{property}', [Owner\PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/properties/{property}', [Owner\PropertyController::class, 'destroy'])->name('properties.destroy');
    Route::delete('/properties/{property}/media/{media}', [Owner\PropertyController::class, 'destroyMedia'])->name('properties.media.destroy');

    // Manajemen Order Sewa Properti
    Route::get('/bookings', [Owner\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [Owner\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/approve', [Owner\BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [Owner\BookingController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{booking}/start', [Owner\BookingController::class, 'start'])->name('bookings.start');
    Route::post('/bookings/{booking}/complete', [Owner\BookingController::class, 'complete'])->name('bookings.complete');
});

/* ---------------------------- PENYEDIA JASA ---------------------------- */

Route::middleware(['auth', 'role:provider'])->prefix('provider')->name('provider.')->group(function () {
    Route::get('/dashboard', [Provider\DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Jasa (Modul 2)
    Route::get('/services', [Provider\ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create', [Provider\ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [Provider\ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}', [Provider\ServiceController::class, 'show'])->name('services.show');
    Route::get('/services/{service}/edit', [Provider\ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}', [Provider\ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [Provider\ServiceController::class, 'destroy'])->name('services.destroy');
    Route::delete('/services/{service}/media/{media}', [Provider\ServiceController::class, 'destroyMedia'])->name('services.media.destroy');

    // Manajemen Order Jasa
    Route::get('/bookings', [Provider\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [Provider\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/approve', [Provider\BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [Provider\BookingController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{booking}/start', [Provider\BookingController::class, 'start'])->name('bookings.start');
    Route::post('/bookings/{booking}/complete', [Provider\BookingController::class, 'complete'])->name('bookings.complete');
});

/* -------------------------------- ADMIN -------------------------------- */

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [Admin\DashboardController::class, 'search'])->name('search');

    // User Management (Settings)
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::put('/users/{user}/role', [Admin\UserController::class, 'updateRole'])->name('users.update-role');
    Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Property Management & Verification
    Route::get('/properties', [Admin\PropertyController::class, 'index'])->name('properties.index');
    Route::post('/properties/{property}/verify', [Admin\PropertyController::class, 'verify'])->name('properties.verify');

    // Service Management & Verification
    Route::get('/services', [Admin\ServiceController::class, 'index'])->name('services.index');
    Route::post('/services/{service}/verify', [Admin\ServiceController::class, 'verify'])->name('services.verify');

    // Transaction Management
    Route::get('/bookings', [Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::delete('/bookings/{booking}', [Admin\BookingController::class, 'destroy'])->name('bookings.destroy');

    // Chat Masuk (widget chat pengguna)
    Route::get('/chats', [Admin\ChatController::class, 'index'])->name('chats.index');
    Route::get('/chats/{conversation}', [Admin\ChatController::class, 'show'])->name('chats.show');
    Route::get('/chats/{conversation}/messages', [Admin\ChatController::class, 'messages'])->name('chats.messages');
    Route::post('/chats/{conversation}/reply', [Admin\ChatController::class, 'reply'])->name('chats.reply');
    Route::post('/chats/{conversation}/toggle', [Admin\ChatController::class, 'toggle'])->name('chats.toggle');

    // Reports
    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');

    // History Log (audit trail)
    Route::get('/activity-logs', [Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::delete('/activity-logs/clear', [Admin\ActivityLogController::class, 'clear'])->name('activity-logs.clear');

    // Pengaturan (Landing Page, SMTP, Branding, Chat & AI)
    Route::get('/settings', [Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/landing', [Admin\SettingController::class, 'updateLanding'])->name('settings.landing');
    Route::put('/settings/smtp', [Admin\SettingController::class, 'updateSmtp'])->name('settings.smtp');
    Route::post('/settings/branding', [Admin\SettingController::class, 'updateBranding'])->name('settings.branding');
    Route::post('/settings/smtp/test', [Admin\SettingController::class, 'testSmtp'])->name('settings.smtp.test');
    Route::put('/settings/chat', [Admin\SettingController::class, 'updateChat'])->name('settings.chat');
    Route::post('/settings/chat/test', [Admin\SettingController::class, 'testGroq'])->name('settings.chat.test');
});
