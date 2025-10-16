<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'habit_id',
        'date',
        'value',
    ];

    protected $casts = [
        'date' => 'date',
        'value' => 'integer',
    ];

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }
}
