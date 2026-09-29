<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\Setting;
use App\Models\User;
use App\Services\GroqChatService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Uji end-to-end fitur Chat & AI Groq:
 * 1. Simpan Groq API key dari pengaturan admin → terbaca kembali (bug union array dulu).
 * 2. Widget chat mode admin (guest → admin dinotifikasi).
 * 3. Widget chat mode AI (bot membalas via Groq API).
 * 4. Tes koneksi Groq dari pengaturan.
 */
class ChatAndAiSettingsTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::firstOrCreate(
            ['email' => 'chat-test-admin@cohub.test'],
            ['name' => 'Admin Uji', 'role' => 'super_admin', 'password' => 'password', 'is_active' => true],
        );
    }

    /** @test */
    public function simpan_groq_api_key_dari_pengaturan_admin_dan_terbaca_kembali(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->put(route('admin.settings.chat'), [
            'chat_ai_enabled' => '1',
            'chat_admin_enabled' => '1',
            'groq_api_key' => 'gsk_TESTKEY_abcdef123456',
            'groq_model' => 'llama-3.3-70b-versatile',
            'chat_welcome_message' => 'Halo, saya asisten AI CoHub!',
        ]);

        $response->assertRedirect();
        $this->assertEquals('gsk_TESTKEY_abcdef123456', Setting::get('groq_api_key'), 'API key harus tersimpan di DB.');

        // Bug lama: union array membuat key selalu terbaca kosong
        $chat = Setting::chat();
        $this->assertEquals('gsk_TESTKEY_abcdef123456', $chat['groq_api_key'], 'Setting::chat() harus mengembalikan key dari DB.');
        $this->assertSame('1', $chat['chat_ai_enabled']);
        $this->assertTrue(GroqChatService::aiEnabled(), 'AI harus aktif ketika key tersimpan + toggle on.');
    }

    /** @test */
    public function guest_bisa_buka_chat_admin_dan_admin_dapat_notifikasi(): void
    {
        $admin = $this->admin();
        Setting::set('chat_admin_enabled', '1');
        Setting::set('chat_ai_enabled', '0');

        $open = $this->postJson(route('chat.open'), ['type' => 'admin', 'name' => 'Tamu Uji', 'email' => 'tamu@tes.id']);
        $open->assertOk()->assertJsonPath('ok', true);

        $convoId = $open->json('conversation.id');
        $token = $open->json('token');
        $this->assertNotNull($token, 'Guest harus menerima token akses.');

        $send = $this->postJson("/chat/{$convoId}/messages", [
            'message' => 'Halo admin, mau tanya sewa ruko',
            'token' => $token,
        ]);
        $send->assertOk()->assertJsonPath('ok', true);
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $convoId,
            'sender' => 'user',
            'message' => 'Halo admin, mau tanya sewa ruko',
        ]);

        // 🔔 Admin harus dapat notifikasi database
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => 'App\Notifications\NewChatMessage',
        ]);
    }

    /** @test */
    public function chat_mode_ai_dibalas_bot_groq(): void
    {
        Setting::set('chat_ai_enabled', '1');
        Setting::set('groq_api_key', 'gsk_FAKE_KEY');
        Setting::set('chat_admin_enabled', '1');

        // Fake respons Groq agar tidak benar-benar memanggil API
        $jawabanBot = 'Untuk menyewa properti: buka halaman Properti → pilih → klik Sewa Sekarang. 🏠';
        Http::fake([
            'groq.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => $jawabanBot]],
                ],
            ], 200),
        ]);
        // (isi jawaban divalidasi lewat assertDatabaseHas di bawah)

        $open = $this->postJson(route('chat.open'), ['type' => 'ai', 'name' => 'Tamu AI']);
        $open->assertOk()->assertJsonPath('ok', true);

        $convoId = $open->json('conversation.id');
        $send = $this->postJson("/chat/{$convoId}/messages", [
            'message' => 'Gimana cara sewa properti?',
            'token' => $open->json('token'),
        ]);

        $send->assertOk();
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $convoId,
            'sender' => 'bot',
            'message' => 'Untuk menyewa properti: buka halaman Properti → pilih → klik Sewa Sekarang. 🏠',
        ]);
    }

    /** @test */
    public function chat_ai_menolak_bila_belum_ada_api_key(): void
    {
        Setting::set('chat_ai_enabled', '0');
        Setting::set('groq_api_key', null);

        $this->postJson(route('chat.open'), ['type' => 'ai'])
            ->assertStatus(423)
            ->assertJsonPath('ok', false);
    }

    /** @test */
    public function tes_koneksi_groq_sukses(): void
    {
        $admin = $this->admin();
        Setting::set('groq_api_key', 'gsk_FAKE_KEY');
        Setting::set('groq_model', 'llama-3.3-70b-versatile');

        Http::fake(['groq.com/*' => Http::response(['choices' => [['message' => ['content' => 'OK']]]], 200)]);

        $response = $this->actingAs($admin)->post(route('admin.settings.chat.test'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function pol_notifikasi_mengembalikan_badge_dan_chat_unread(): void
    {
        $admin = $this->admin();

        $convo = ChatConversation::create([
            'guest_name' => 'Tamu Poll', 'type' => 'admin', 'status' => 'open',
            'admin_unread' => 2, 'last_message_at' => now(),
        ]);

        $this->actingAs($admin)
            ->getJson(route('notifications.poll'))
            ->assertOk()
            ->assertJsonPath('chat_unread', 2)
            ->assertJsonStructure(['unread', 'items']);
    }
}
