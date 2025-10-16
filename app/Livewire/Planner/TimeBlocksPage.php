<?php

namespace App\Livewire\Planner;

use App\Services\TimeBlockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class TimeBlocksPage extends Component
{
    public string $selectedDate;

    public ?int $newTimeBlockTaskId = null;

    public string $newTimeBlockStart = '09:00';

    public string $newTimeBlockEnd = '10:00';

    public string $newTimeBlockNote = '';

    protected TimeBlockService $timeBlockService;

    public function boot(TimeBlockService $timeBlockService): void
    {
        $this->timeBlockService = $timeBlockService;
    }

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    public function updatedSelectedDate(string $value): void
    {
        try {
            $this->selectedDate = Carbon::parse($value)->toDateString();
        } catch (\Throwable $throwable) {
            $this->addError('selectedDate', __('Tanggal tidak valid.'));
        }
    }

    public function createTimeBlock(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->validate([
            'newTimeBlockStart' => ['required', 'date_format:H:i'],
            'newTimeBlockEnd' => ['required', 'date_format:H:i'],
            'newTimeBlockTaskId' => ['nullable', 'integer'],
            'newTimeBlockNote' => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->newTimeBlockTaskId) {
            $task = $user->tasks()->find($this->newTimeBlockTaskId);
            if (! $task) {
                throw ValidationException::withMessages([
                    'newTimeBlockTaskId' => __('Task tidak ditemukan.'),
                ]);
            }
        }

        $start = Carbon::parse("{$this->selectedDate} {$this->newTimeBlockStart}", config('app.timezone'));
        $end = Carbon::parse("{$this->selectedDate} {$this->newTimeBlockEnd}", config('app.timezone'));

        $this->timeBlockService->create($user, [
            'task_id' => $this->newTimeBlockTaskId,
            'start_at' => $start,
            'end_at' => $end,
            'note' => $this->newTimeBlockNote,
        ]);

        $this->newTimeBlockNote = '';
        $this->dispatch('toast', body: __('Time block dibuat'));
    }

    public function deleteTimeBlock(int $blockId): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $block = $user->timeBlocks()->find($blockId);

        if ($block) {
            $block->delete();
            $this->dispatch('toast', body: __('Time block dihapus'));
        }
    }

    public function render(): View
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $tasks = $user->tasks()
            ->where(function (Builder $query) {
                $query->whereNull('planned_date')
                    ->orWhereDate('planned_date', $this->selectedDate);
            })
            ->orderByRaw('planned_date IS NULL desc')
            ->orderBy('planned_date')
            ->orderBy('order')
            ->orderBy('title')
            ->get();

        $timeBlocks = $user->timeBlocks()
            ->whereDate('start_at', $this->selectedDate)
            ->orderBy('start_at')
            ->get();

        return view('livewire.planner.time-blocks-page', [
            'timeBlocks' => $timeBlocks,
            'tasks' => $tasks,
        ]);
    }
}
