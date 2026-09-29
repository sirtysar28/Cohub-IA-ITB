<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // ID_Pemilik
            $table->foreignId('category_id')->constrained('categories');
            $table->string('name');                       // Nama_Properti
            $table->text('description')->nullable();      // Deskripsi_Properti
            $table->decimal('land_size', 10, 2)->nullable();       // Luas Tanah (m2)
            $table->decimal('building_size', 10, 2)->nullable();   // Luas Bangunan (m2)
            $table->string('provinsi')->nullable();
            $table->string('kota_kabupaten')->nullable();  // lokasi pencarian
            $table->string('kecamatan')->nullable();
            $table->text('address')->nullable();
            $table->string('status', 20)->default('available');   // available | rented | maintenance | inactive
            $table->string('verification', 20)->default('pending'); // pending | approved | rejected
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'verification']);
            $table->index(['kota_kabupaten']);
            $table->index(['category_id']);
        });

        // Media properti (maks 5 file, file pertama = thumbnail)
        Schema::create('property_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('type', 10)->default('image'); // image | video
            $table->boolean('is_thumbnail')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Harga sewa multi periode (per hari / bulan / tahun)
        Schema::create('property_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('period', 10); // daily | monthly | yearly
            $table->decimal('price', 15, 2);
            $table->timestamps();
            $table->unique(['property_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_prices');
        Schema::dropIfExists('property_media');
        Schema::dropIfExists('properties');
    }
};
