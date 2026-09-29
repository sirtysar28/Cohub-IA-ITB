<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // ID_Penyedia
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name');                        // Nama_Jasa
            $table->text('description')->nullable();       // Deskripsi_Jasa
            $table->decimal('starting_price', 15, 2);      // Harga_Mulai_Dari
            $table->string('price_unit', 20)->default('per_project'); // per_hour|per_day|per_project|per_pax|per_unit
            $table->string('status', 20)->default('available'); // available | unavailable
            $table->string('verification', 20)->default('pending');
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'verification']);
            $table->index(['category_id']);
            $table->index(['starting_price']);
        });

        // Portfolio / gambar jasa (maks 5, pertama = thumbnail)
        Schema::create('service_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('type', 10)->default('image');
            $table->boolean('is_thumbnail')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Availability jasa: Senin-Sabtu, jam 00:00-24:00 (FR-SVC-04)
        Schema::create('service_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=Senin .. 6=Sabtu
            $table->time('start_time')->default('00:00:00');
            $table->time('end_time')->default('24:00:00')->nullable();
            $table->timestamps();
            $table->unique(['service_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_availabilities');
        Schema::dropIfExists('service_media');
        Schema::dropIfExists('services');
    }
};
