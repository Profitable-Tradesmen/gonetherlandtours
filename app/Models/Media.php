<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['widths' => 'array'];
    }

    public function url(?int $width = null): string
    {
        $widths = $this->widths ?: [];
        $w = $width && in_array($width, $widths) ? $width : (max($widths) ?: null);

        // asset() follows the host the visitor is on, so images work on .test, 127.0.0.1:8000 and live alike (APP_URL need not match).
        $file = storage_path('app/public/'.$this->path.'-'.$w.'.webp');

        // ?v= changes when the file does, so a browser or CDN never keeps serving an older image saved under the same name.
        return asset('storage/'.$this->path.'-'.$w.'.webp').(is_file($file) ? '?v='.filemtime($file) : '');
    }

    public function srcset(): string
    {
        return collect($this->widths)->map(fn ($w) => $this->url($w).' '.$w.'w')->implode(', ');
    }
}
