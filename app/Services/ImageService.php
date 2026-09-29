<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * ImageService — validasi + kompresi otomatis berkas gambar (NFR: optimasi bandwidth).
 * Video disimpan apa adanya (dibatasi ukuran).
 */
class ImageService
{
    public const MAX_IMAGES = 5;         // maks 5 gambar/video (FR-PROP-02 / FR-SVC-03)
    public const MAX_IMAGE_SIZE = 4096;  // px sisi terpanjang
    public const JPEG_QUALITY = 82;

    /**
     * Simpan kumpulan file media; file pertama otomatis menjadi thumbnail.
     *
     * @param  \Illuminate\Http\UploadedFile[]  $files
     * @return array<int, array{path:string, type:string, is_thumbnail:bool, sort_order:int}>
     */
    public function storeMedia(array $files, string $directory): array
    {
        $media = [];
        $order = 0;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $isVideo = str_starts_with($file->getMimeType(), 'video/');
            $path = $isVideo
                ? $file->store($directory, 'public')
                : $this->storeCompressedImage($file, $directory);

            $media[] = [
                'path' => $path,
                'type' => $isVideo ? 'video' : 'image',
                'is_thumbnail' => $order === 0, // file pertama = thumbnail
                'sort_order' => $order,
            ];
            $order++;
        }

        return $media;
    }

    /** Kompres & resize gambar dengan GD, lalu simpan ke disk public. */
    protected function storeCompressedImage(UploadedFile $file, string $directory): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = uniqid(now()->format('YmdHis') . '_') . '.' . $ext;
        $target = rtrim($directory, '/') . '/' . $filename;

        $source = $file->getRealPath();
        $info = @getimagesize($source);

        if (! $info) {
            return $file->storeAs($directory, $filename, 'public'); // fallback
        }

        [$width, $height] = $info;
        $mime = $info['mime'];

        // Skala jika melebihi batas
        $scale = min(1, self::MAX_IMAGE_SIZE / max($width, $height));
        $newW = (int) round($width * $scale);
        $newH = (int) round($height * $scale);

        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($source),
            'image/png' => @imagecreatefrompng($source),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : null,
            'image/gif' => @imagecreatefromgif($source),
            default => null,
        };

        if (! $src) {
            return $file->storeAs($directory, $filename, 'public');
        }

        $dst = imagecreatetruecolor($newW, $newH);

        // Pertahankan transparansi PNG
        if (in_array($mime, ['image/png', 'image/gif', 'image/webp'], true)) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagedestroy($src);

        ob_start();
        switch ($mime) {
            case 'image/png':
                imagepng($dst, null, 6);
                break;
            case 'image/webp':
                imagewebp($dst, null, self::JPEG_QUALITY);
                break;
            case 'image/gif':
                imagegif($dst);
                break;
            default:
                imagejpeg($dst, null, self::JPEG_QUALITY);
        }
        $binary = ob_get_clean();
        imagedestroy($dst);

        Storage::disk('public')->put($target, $binary);

        return $target;
    }

    /** Hapus media lama dari storage. */
    public function deleteMediaPaths(iterable $paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
