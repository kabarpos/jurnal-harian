<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const PRIORITY_P1 = 'p1';
    public const PRIORITY_P2 = 'p2';
    public const PRIORITY_P3 = 'p3';
    public const PRIORITY_P4 = 'p4';

    public const STATUS_PLANNED = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'description',
        'priority',
        'status',
        'planned_date',
        'due_date',
        'estimate_minutes',
        'actual_minutes',
        'context',
        'is_recurring',
        'recurrence_rule',
        'order',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'due_date' => 'date',
        'estimate_minutes' => 'integer',
        'actual_minutes' => 'integer',
        'is_recurring' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function timeBlocks(): HasMany
    {
        return $this->hasMany(TimeBlock::class);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    public function scopeForDate(Builder $query, Carbon|string $date): Builder
    {
        $date = $date instanceof Carbon ? $date->toDateString() : $date;

        return $query->whereDate('planned_date', $date);
    }

    public function scopeBacklog(Builder $query): Builder
    {
        return $query->whereNull('planned_date');
    }
}
