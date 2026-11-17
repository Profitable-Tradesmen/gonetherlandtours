<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A header or footer menu entry managed in Admin > Navigation. */
class MenuItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'new_tab' => 'boolean'];
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->where('is_active', true)->orderBy('sort_order');
    }

    public function getLocationLabelAttribute(): string
    {
        $where = $this->menu === 'footer' ? 'Footer' : 'Header';
        if ($this->parent_id && ($p = static::find($this->parent_id))) {
            return $where.' > '.$p->label.' (card)';
        }

        return $where.match ($this->kind) { 'tours' => ' (Tours menu)', 'cards' => ' (dropdown)', default => '' };
    }

    public function getLinkLabelAttribute(): string
    {
        return $this->kind === 'tours' ? '(automatic)' : (string) $this->url;
    }

    /** Top-level active items of a menu, with their active children. */
    public static function tree(string $menu)
    {
        return static::where('menu', $menu)->whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->with('children')->get();
    }

    /** Absolute link for this item. A relative /path is made absolute with url(). */
    public function href(): string
    {
        $u = trim((string) $this->url);
        if ($u === '' || $u === '/') {
            return url('/');
        }

        return str_starts_with($u, '/') ? url($u) : $u;
    }
}
