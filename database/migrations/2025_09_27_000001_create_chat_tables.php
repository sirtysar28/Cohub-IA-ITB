<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Percakapan chat (widget pojok kanan bawah)
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // null = guest
            $table->string('guest_name', 100)->nullable();
            $table->string('guest_email', 255)->nullable();
            $table->string('token', 64);                    // token akses guest (UUID)
            $table->string('type', 10)->default('admin');   // admin | ai
            $table->string('status', 10)->default('open');  // open | closed
            $table->unsignedInteger('admin_unread')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'last_message_at']);
            $table->index('user_id');
        });

        // Pesan dalam percakapan
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->string('sender', 10);                   // user | admin | bot
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete(); // admin pembalas
            $table->text('message');
            $table->timestamps();

            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
