<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Chatbot AI berbasis Groq (https://console.groq.com).
 * API key & model dikelola dari Admin → Pengaturan → Chat & AI.
 * Jawaban chatbot bersumber dari knowledge base workflow CoHub
 * (resources/knowledge/cohub-workflow.md).
 */
class GroqChatService
{
    public const API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    public const MODELS = [
        'llama-3.3-70b-versatile' => 'Llama 3.3 70B Versatile (direkomendasikan)',
        'llama-3.1-8b-instant' => 'Llama 3.1 8B Instant (tercepat)',
        'openai/gpt-oss-120b' => 'GPT-OSS 120B',
        'openai/gpt-oss-20b' => 'GPT-OSS 20B',
        'qwen/qwen3-32b' => 'Qwen 3 32B',
    ];

    /** Setting chat & AI (dengan default). */
    public static function settings(): array
    {
        return Setting::chat();
    }

    public static function aiEnabled(): bool
    {
        $s = self::settings();

        return ($s['chat_ai_enabled'] ?? '0') === '1' && ! empty($s['groq_api_key']);
    }

    public static function adminChatEnabled(): bool
    {
        return (self::settings()['chat_admin_enabled'] ?? '1') === '1';
    }

    /** Balasan AI untuk pesan terakhir dalam percakapan. */
    public function reply(ChatConversation $conversation, string $userMessage): string
    {
        $settings = self::settings();
        $apiKey = $settings['groq_api_key'] ?? '';
        $model = $settings['groq_model'] ?: 'llama-3.3-70b-versatile';

        if (empty($apiKey)) {
            return $this->fallback('Maaf, asisten AI belum dikonfigurasi. Admin dapat mengisi Groq API Key di menu **Pengaturan → Chat & AI**. Sementara itu, silakan gunakan mode **Chat Admin** ya! 😊');
        }

        $history = $conversation->messages()
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->reverse()
            ->map(fn ($m) => [
                'role' => $m->sender === 'user' ? 'user' : 'assistant',
                'content' => $m->message,
            ])
            ->values()
            ->all();

        try {
            $response = Http::withToken($apiKey)
                ->timeout(45)
                ->retry(2, 500)
                ->post(self::API_URL, [
                    'model' => $model,
                    'messages' => array_merge($this->systemMessages($conversation), $history),
                    'temperature' => 0.4,
                    'max_tokens' => 700,
                ]);

            if ($response->failed()) {
                report("Groq API error [{$response->status()}]: ".$response->body());

                return $this->fallback('Maaf, asisten AI sedang mengalami gangguan ('.$response->status().'). Silakan coba lagi atau gunakan mode **Chat Admin**.');
            }

            $content = $response->json('choices.0.message.content');

            return trim((string) ($content ?? '')) ?: $this->fallback();
        } catch (ConnectionException $e) {
            report($e);

            return $this->fallback('Maaf, koneksi ke server AI timeout. Silakan coba lagi atau gunakan mode **Chat Admin**.');
        } catch (\Throwable $e) {
            report($e);

            return $this->fallback();
        }
    }

    /** Tes koneksi API Groq dari halaman pengaturan admin. */
    public function testConnection(): array
    {
        $settings = self::settings();
        $apiKey = $settings['groq_api_key'] ?? '';

        if (empty($apiKey)) {
            return ['ok' => false, 'message' => 'Groq API Key masih kosong. Isi lalu simpan dulu.'];
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post(self::API_URL, [
                    'model' => $settings['groq_model'] ?: 'llama-3.3-70b-versatile',
                    'messages' => [
                        ['role' => 'user', 'content' => 'Balas hanya dengan kata: OK'],
                    ],
                    'max_tokens' => 5,
                ]);

            if ($response->successful()) {
                return ['ok' => true, 'message' => 'Koneksi Groq berhasil ✅ Model: '.($settings['groq_model'] ?: 'llama-3.3-70b-versatile')];
            }

            $error = $response->json('error.message') ?: $response->body();

            return ['ok' => false, 'message' => 'Groq merespons error '.$response->status().': '.mb_strimwidth((string) $error, 0, 200, '…')];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Gagal terhubung: '.$e->getMessage()];
        }
    }

    /** System prompt: knowledge base workflow + konteks pengguna. */
    protected function systemMessages(ChatConversation $conversation): array
    {
        $knowledge = file_get_contents(base_path('resources/knowledge/cohub-workflow.md')) ?: '';

        $context = 'Konteks pengguna yang sedang bertanya: ';
        if ($user = $conversation->user) {
            $context .= "sudah login sebagai {$user->role->label()} dengan nama {$user->name}.";
        } else {
            $context .= 'tamu yang belum login (umumnya calon customer).';
        }

        return [
            [
                'role' => 'system',
                'content' => implode("\n\n", [
                    'Kamu adalah "Asisten AI CoHub" — chatbot ramah aplikasi CoHub IA ITB Jatim (platform sewa properti & jasa profesional).',
                    'Jawab SELALU dalam Bahasa Indonesia, singkat, jelas, dan ramah. Gunakan emoji secukupnya. Boleh pakai format daftar/bold sederhana (markdown minimal).',
                    'Basis pengetahuanmu adalah dokumen berikut — jawab HANYA berdasarkan isi dokumen ini:',
                    "<<< KNOWLEDGE BASE >>>\n{$knowledge}\n<<< AKHIR KNOWLEDGE BASE >>>",
                    $context,
                    'Jika pertanyaan di luar dokumen (mis. harga spesifik barang, politik, dll), katakan sopan bahwa itu di luar cakupanmu lalu arahkan ke topik CoHub atau sarankan mode "Chat Admin".',
                ]),
            ],
        ];
    }

    protected function fallback(?string $message = null): string
    {
        return $message ?? 'Maaf, terjadi kendala saat memproses jawaban. Silakan coba lagi atau gunakan mode **Chat Admin** untuk dibantu langsung oleh tim kami. 🙏';
    }
}
