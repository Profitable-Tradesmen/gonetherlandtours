<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'noindex' => 'boolean', 'published_at' => 'date'];
    }

    /** Published, and not scheduled for a future date. Newest first. */
    public function scopePublished($q)
    {
        return $q->where('is_published', true)
            ->where(fn ($w) => $w->whereNull('published_at')->orWhereDate('published_at', '<=', now()->toDateString()))
            ->orderByDesc('published_at')->orderByDesc('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Shown in the admin list. */
    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_published) {
            return 'Draft';
        }

        return $this->published_at && $this->published_at->isFuture() ? 'Scheduled' : 'Published';
    }
}
