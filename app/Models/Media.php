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

        return Storage::disk('public')->url($this->path.'-'.$w.'.webp');
    }

    public function srcset(): string
    {
        return collect($this->widths)->map(fn ($w) => $this->url($w).' '.$w.'w')->implode(', ');
    }
}
