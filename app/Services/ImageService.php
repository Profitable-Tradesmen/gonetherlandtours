<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Validates, auto-resizes and converts uploaded images to responsive WebP files (GD).
 * Files land in storage/app/public/media/YYYY/ as <base>-<width>.webp.
 */
class ImageService
{
    public const WIDTHS = [480, 960, 1600];

    public function store(UploadedFile $file, string $alt): Media
    {
        $info = @getimagesize($file->getRealPath());
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new RuntimeException('Unsupported image type. Use JPG, PNG or WebP.');
        }

        $src = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($file->getRealPath()),
            IMAGETYPE_PNG => imagecreatefrompng($file->getRealPath()),
            IMAGETYPE_WEBP => imagecreatefromwebp($file->getRealPath()),
        };
        if (! $src) {
            throw new RuntimeException('Could not read the image.');
        }
        imagepalettetotruecolor($src);
        imagealphablending($src, true);
        imagesavealpha($src, true);

        [$w, $h] = [imagesx($src), imagesy($src)];
        $base = 'media/'.date('Y').'/'.Str::lower(Str::random(14));
        $widths = [];

        foreach (self::WIDTHS as $target) {
            if ($target > $w && $widths) {
                continue; // never upscale beyond the original, but always write at least one size
            }
            $tw = min($target, $w);
            $th = (int) round($h * ($tw / $w));
            $dst = imagecreatetruecolor($tw, $th);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
            ob_start();
            imagewebp($dst, null, 82);
            Storage::disk('public')->put($base.'-'.$tw.'.webp', ob_get_clean());
            $widths[] = $tw;
        }

        return Media::create([
            'path' => $base,
            'widths' => $widths,
            'width' => max($widths),
            'height' => (int) round($h * (max($widths) / $w)),
            'alt' => $alt,
            'original_name' => Str::limit($file->getClientOriginalName(), 200, ''),
        ]);
    }

    public function delete(Media $media): void
    {
        foreach ($media->widths as $w) {
            Storage::disk('public')->delete($media->path.'-'.$w.'.webp');
        }
        $media->delete();
    }
}
