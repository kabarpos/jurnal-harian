<?php

namespace App\Services;

use App\Models\TimeBlock;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TimeBlockService
{
    public function create(User $user, array $attributes): TimeBlock
    {
        $start = Carbon::parse($attributes['start_at']);
        $end = Carbon::parse($attributes['end_at']);

        $this->validateWindow($start, $end);
        $this->ensureNoOverlap($user, $start, $end, null);

        return $user->timeBlocks()->create([
            'task_id' => $attributes['task_id'] ?? null,
            'start_at' => $start,
            'end_at' => $end,
            'note' => $attributes['note'] ?? null,
        ]);
    }

    public function update(TimeBlock $block, array $attributes): TimeBlock
    {
        $start = Carbon::parse($attributes['start_at']);
        $end = Carbon::parse($attributes['end_at']);

        $this->validateWindow($start, $end);
        $this->ensureNoOverlap($block->user, $start, $end, $block->id);

        $block->fill([
            'task_id' => $attributes['task_id'] ?? null,
            'start_at' => $start,
            'end_at' => $end,
            'note' => $attributes['note'] ?? null,
        ])->save();

        return $block;
    }

    protected function validateWindow(Carbon $start, Carbon $end): void
    {
        if ($start->gte($end)) {
            throw ValidationException::withMessages([
                'end_at' => __('End time must be after the start time.'),
            ]);
        }

        if ($start->diffInMinutes($end) % 15 !== 0) {
            throw ValidationException::withMessages([
                'end_at' => __('Time blocks must align to 15 minute increments.'),
            ]);
        }
    }

    protected function ensureNoOverlap(User $user, Carbon $start, Carbon $end, ?int $ignoreId): void
    {
        $query = $user->timeBlocks()
            ->where(function ($q) use ($start, $end) {
                $q->where('start_at', '<', $end)
                    ->where('end_at', '>', $start);
            });

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'start_at' => __('Time block overlaps with an existing block.'),
            ]);
        }
    }
}
