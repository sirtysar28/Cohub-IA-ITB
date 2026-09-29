<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Booking terpadu: properti & jasa dipisah (FR-TRX-03)
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();          // BK-20240101-0001
            $table->string('type', 10);                    // property | service
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date')->nullable();        // sewa properti / tanggal jadwal jasa
            $table->date('end_date')->nullable();          // sewa properti / periode rental tools
            $table->time('schedule_time')->nullable();     // jam pelaksanaan jasa
            $table->unsignedInteger('quantity')->default(1); // pax / unit
            $table->string('price_period', 10)->nullable();  // daily | monthly | yearly (properti)
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->text('purpose')->nullable();           // tujuan penggunaan properti (FR-TRX-01)
            $table->text('note')->nullable();              // custom note / brief (FR-TRX-02)
            $table->string('status', 25)->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['property_id']);
            $table->index(['service_id']);
        });

        // Riwayat status (state engine audit)
        Schema::create('booking_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 25)->nullable();
            $table->string('to_status', 25);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Pembayaran
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_no', 30)->unique();
            $table->decimal('amount', 15, 2);
            $table->string('method', 30)->default('transfer'); // transfer | midtrans | manual
            $table->string('status', 20)->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // Notifikasi database Laravel
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_status_histories');
        Schema::dropIfExists('bookings');
    }
};
