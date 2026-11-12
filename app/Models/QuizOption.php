<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizOption extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function step()
    {
        return $this->belongsTo(QuizStep::class, 'quiz_step_id');
    }
}
