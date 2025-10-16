<?php

namespace App\Livewire\Planner;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class DailyPlannerPage extends Component
{
    public string $selectedDate;

    public string $newPlannedTaskTitle = '';

    public string $newBacklogTaskTitle = '';

    public string $newTaskPriority = Task::PRIORITY_NORMAL;

    public ?int $newTaskProjectId = null;

    public ?int $editingTaskId = null;

    public string $editingTaskTitle = '';

    public string $editingTaskPriority = Task::PRIORITY_NORMAL;

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
            'newTaskPriority' => ['required', 'in:'.implode(',', Task::PRIORITIES)],
        ]);

        $user->tasks()->create([
            'title' => $this->newPlannedTaskTitle,
            'priority' => $this->newTaskPriority,
            'planned_date' => $this->selectedDate,
            'status' => Task::STATUS_PLANNED,
            'order' => $this->nextOrderFor($this->selectedDate),
        ]);

        $this->newPlannedTaskTitle = '';
        $this->newTaskPriority = Task::PRIORITY_NORMAL;
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
            'newTaskPriority' => ['required', 'in:'.implode(',', Task::PRIORITIES)],
        ]);

        $user->tasks()->create([
            'title' => $this->newBacklogTaskTitle,
            'priority' => $this->newTaskPriority,
            'status' => Task::STATUS_PLANNED,
            'order' => $this->nextOrderFor(null),
        ]);

        $this->newBacklogTaskTitle = '';
        $this->newTaskPriority = Task::PRIORITY_NORMAL;
        $this->dispatch('toast', body: __('Task added to backlog'));
    }

    public function startEditingBacklogTask(int $taskId): void
    {
        $task = $this->findTask($taskId);

        if (! $task || $task->planned_date !== null) {
            return;
        }

        $this->editingTaskId = $task->id;
        $this->editingTaskTitle = $task->title;
        $this->editingTaskPriority = $task->priority;
        $this->resetErrorBag();
    }

    public function updateBacklogTask(): void
    {
        if (! $this->editingTaskId) {
            return;
        }

        $task = $this->findTask($this->editingTaskId);

        if (! $task || $task->planned_date !== null) {
            $this->cancelEditingBacklogTask();

            return;
        }

        $this->validate([
            'editingTaskTitle' => ['required', 'string', 'max:255'],
            'editingTaskPriority' => ['required', 'in:'.implode(',', Task::PRIORITIES)],
        ]);

        $task->update([
            'title' => $this->editingTaskTitle,
            'priority' => $this->editingTaskPriority,
        ]);

        $this->dispatch('toast', body: __('Backlog task updated'));
        $this->cancelEditingBacklogTask();
    }

    public function cancelEditingBacklogTask(): void
    {
        $this->editingTaskId = null;
        $this->editingTaskTitle = '';
        $this->editingTaskPriority = Task::PRIORITY_NORMAL;
        $this->resetErrorBag();
    }

    public function deleteTask(int $taskId): void
    {
        $task = $this->findTask($taskId);
        if (! $task || $task->planned_date !== null) {
            return;
        }

        $task->delete();

        if ($this->editingTaskId === $taskId) {
            $this->cancelEditingBacklogTask();
        }

        $this->dispatch('toast', body: __('Backlog task removed'));
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

        return view('livewire.planner.daily-planner-page', [
            'backlogTasks' => $backlogTasks,
            'plannedTasks' => $plannedTasks,
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
