<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TimeBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'start_at',
        'end_at',
        'note',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function durationMinutes(): int
    {
        /** @var Carbon $start */
        $start = $this->start_at;
        /** @var Carbon $end */
        $end = $this->end_at;

        return (int) floor($start->diffInMinutes($end));
    }
}
