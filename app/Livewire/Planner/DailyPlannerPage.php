<?php

namespace App\Livewire\Planner;

use App\Models\Task;
use App\Services\TimeBlockService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class DailyPlannerPage extends Component
{
    public string $selectedDate;

    public string $newPlannedTaskTitle = '';

    public string $newBacklogTaskTitle = '';

    public string $newTaskPriority = Task::PRIORITY_P3;

    public ?int $newTaskProjectId = null;

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

    #[On('refresh-planner')]
    public function refreshPlanner(): void
    {
        $this->resetErrorBag();
    }

    public function updatedSelectedDate(string $value): void
    {
        try {
            $this->selectedDate = Carbon::parse($value)->toDateString();
        } catch (\Throwable $throwable) {
            $this->addError('selectedDate', __('Invalid date selected.'));
        }
    }

    public function createPlannedTask(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->validate([
            'newPlannedTaskTitle' => ['required', 'string', 'max:255'],
            'newTaskPriority' => ['required', 'in:p1,p2,p3,p4'],
        ]);

        $user->tasks()->create([
            'title' => $this->newPlannedTaskTitle,
            'priority' => $this->newTaskPriority,
            'planned_date' => $this->selectedDate,
            'status' => Task::STATUS_PLANNED,
            'order' => $this->nextOrderFor($this->selectedDate),
        ]);

        $this->newPlannedTaskTitle = '';
        $this->dispatch('toast', body: __('Task planned for :date', ['date' => $this->selectedDate]));
    }

    public function createBacklogTask(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->validate([
            'newBacklogTaskTitle' => ['required', 'string', 'max:255'],
            'newTaskPriority' => ['required', 'in:p1,p2,p3,p4'],
        ]);

        $user->tasks()->create([
            'title' => $this->newBacklogTaskTitle,
            'priority' => $this->newTaskPriority,
            'status' => Task::STATUS_PLANNED,
            'order' => $this->nextOrderFor(null),
        ]);

        $this->newBacklogTaskTitle = '';
        $this->dispatch('toast', body: __('Task added to backlog'));
    }

    public function toggleDone(int $taskId): void
    {
        $task = $this->findTask($taskId);
        if (! $task) {
            return;
        }

        $task->status = $task->status === Task::STATUS_DONE
            ? Task::STATUS_PLANNED
            : Task::STATUS_DONE;

        if ($task->status === Task::STATUS_DONE && $task->actual_minutes === 0) {
            $task->actual_minutes = $task->estimate_minutes;
        }

        $task->save();
    }

    public function planTask(int $taskId): void
    {
        $task = $this->findTask($taskId);
        if (! $task) {
            return;
        }

        $task->update([
            'planned_date' => $this->selectedDate,
            'order' => $this->nextOrderFor($this->selectedDate),
        ]);
    }

    public function moveTaskToBacklog(int $taskId): void
    {
        $task = $this->findTask($taskId);
        if (! $task) {
            return;
        }

        $task->update([
            'planned_date' => null,
            'order' => $this->nextOrderFor(null),
        ]);
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
            $task = $this->findTask($this->newTimeBlockTaskId);
            if (! $task) {
                throw ValidationException::withMessages([
                    'newTimeBlockTaskId' => __('Task not found'),
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
        $this->dispatch('toast', body: __('Time block created'));
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
        }
    }

    public function render(): View
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $backlogTasks = $user->tasks()
            ->backlog()
            ->orderBy('order')
            ->orderBy('created_at')
            ->get();

        $plannedTasks = $user->tasks()
            ->forDate($this->selectedDate)
            ->orderBy('order')
            ->orderBy('created_at')
            ->get();

        $timeBlocks = $user->timeBlocks()
            ->whereDate('start_at', $this->selectedDate)
            ->orderBy('start_at')
            ->get();

        return view('livewire.planner.daily-planner-page', [
            'backlogTasks' => $backlogTasks,
            'plannedTasks' => $plannedTasks,
            'timeBlocks' => $timeBlocks,
        ]);
    }

    protected function findTask(int $taskId): ?Task
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        return $user->tasks()->find($taskId);
    }

    protected function nextOrderFor(?string $plannedDate): int
    {
        $user = auth()->user();
        if (! $user) {
            return 1;
        }

        $query = $user->tasks();

        if ($plannedDate === null) {
            $query->whereNull('planned_date');
        } else {
            $query->whereDate('planned_date', $plannedDate);
        }

        $max = (int) ($query->max('order') ?? 0);

        return $max + 1;
    }
}
