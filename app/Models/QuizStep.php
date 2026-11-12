<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizStep extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function options()
    {
        return $this->hasMany(QuizOption::class)->orderBy('sort_order');
    }

    public function activeOptions()
    {
        return $this->options()->where('is_active', true);
    }

    /** Active steps with active options, shaped for the Alpine quiz. */
    public static function forQuiz(): array
    {
        return static::where('is_active', true)->orderBy('sort_order')->with('activeOptions')->get()
            ->map(fn ($s) => [
                'key' => $s->key,
                'question' => $s->question,
                'type' => $s->type,
                'options' => $s->activeOptions->map(fn ($o) => [
                    'label' => $o->label, 'value' => $o->value, 'icon' => $o->icon,
                ])->values()->all(),
            ])->values()->all();
    }
}
