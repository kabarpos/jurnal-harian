@php
    $backlogTasks = $backlogTasks ?? collect();
    $plannedTasks = $plannedTasks ?? collect();
    $selectedDateString = $selectedDate ?? now()->toDateString();
    $selectedDateCarbon = \Illuminate\Support\Carbon::parse($selectedDateString);
    $priorityLabels = [
        \App\Models\Task::PRIORITY_NORMAL => __('Normal'),
        \App\Models\Task::PRIORITY_IMPORTANT => __('Important'),
        \App\Models\Task::PRIORITY_URGENT => __('Urgent'),
    ];
    $priorityBadgeClasses = [
        \App\Models\Task::PRIORITY_NORMAL => 'bg-neutral-100 text-neutral-700 ring-1 ring-neutral-300',
        \App\Models\Task::PRIORITY_IMPORTANT => 'bg-amber-100 text-amber-700 ring-1 ring-amber-300',
        \App\Models\Task::PRIORITY_URGENT => 'bg-rose-100 text-rose-700 ring-1 ring-rose-300',
    ];
    $legacyPriorityMap = [
        'p1' => \App\Models\Task::PRIORITY_URGENT,
        'p2' => \App\Models\Task::PRIORITY_IMPORTANT,
        'p3' => \App\Models\Task::PRIORITY_NORMAL,
        'p4' => \App\Models\Task::PRIORITY_NORMAL,
    ];

    foreach ($legacyPriorityMap as $legacyValue => $normalizedValue) {
        $priorityLabels[$legacyValue] = $priorityLabels[$normalizedValue];
        $priorityBadgeClasses[$legacyValue] = $priorityBadgeClasses[$normalizedValue];

        $priorityLabels[strtoupper($legacyValue)] = $priorityLabels[$normalizedValue];
        $priorityBadgeClasses[strtoupper($legacyValue)] = $priorityBadgeClasses[$normalizedValue];
    }
@endphp

<div class="flex h-full flex-col gap-6">
    <header class="flex flex-wrap items-center gap-4">
        <flux:heading size="xl">{{ __('Daily Planner') }}</flux:heading>

        <div class="flex items-center gap-3">
            <label class="text-sm font-medium text-neutral-500 dark:text-neutral-400" for="planner-date">
                {{ __('Focus date') }}
            </label>
            <input
                id="planner-date"
                type="date"
                wire:model.live="selectedDate"
                class="rounded-lg border border-neutral-300 bg-transparent px-3 py-2 text-sm dark:border-neutral-700"
            >
        </div>

        <div class="ml-auto flex gap-3 text-xs text-neutral-500 dark:text-neutral-400">
            <span>{{ __('Shortcuts:') }}</span>
            <span><kbd class="rounded border px-1">n</kbd> {{ __('new task') }}</span>
            <span><kbd class="rounded border px-1">space</kbd> {{ __('toggle status') }}</span>
            <span><kbd class="rounded border px-1">{{ __('/') }}</kbd> {{ __('focus search') }}</span>
        </div>
    </header>

    <section class="grid gap-6 xl:grid-cols-2">
        <article class="flex flex-col rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <header class="flex items-center justify-between border-b border-neutral-200 px-4 py-3 dark:border-neutral-800">
                <flux:heading size="sm">{{ __('Backlog') }}</flux:heading>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('Unscheduled tasks') }}</span>
            </header>
            <div class="flex flex-1 flex-col gap-3 p-4">
                <form wire:submit.prevent="createBacklogTask" class="flex items-center gap-2">
                    <input
                        type="text"
                        required
                        wire:model.defer="newBacklogTaskTitle"
                        placeholder="{{ __('Quick add task...') }}"
                        class="w-full rounded-lg border border-neutral-300 bg-transparent px-3 py-2 text-sm focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:border-neutral-700"
                    >
                    <select
                        wire:model.defer="newTaskPriority"
                        class="rounded-lg border border-neutral-300 bg-neutral-50 px-2 py-2 text-xs font-medium text-neutral-700 transition dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                    >
                        @foreach (\App\Models\Task::PRIORITIES as $priority)
                            <option value="{{ $priority }}">{{ $priorityLabels[$priority] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-sky-600 px-3 py-2 text-sm font-medium text-white shadow hover:bg-sky-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500">
                        {{ __('Add') }}
                    </button>
                </form>

                <div class="flex flex-col gap-2 overflow-y-auto">
                    @forelse ($backlogTasks as $task)
                        <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-3 text-sm shadow-sm transition hover:border-primary-200 hover:bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-800 dark:hover:border-primary-500 dark:hover:bg-neutral-700">
                            @if ($editingTaskId === $task->id)
                                <form wire:submit.prevent="updateBacklogTask" class="flex flex-col gap-2">
                                    <input
                                        type="text"
                                        wire:model.defer="editingTaskTitle"
                                        class="w-full rounded-lg border border-neutral-300 bg-transparent px-3 py-2 text-sm focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:border-neutral-700"
                                        placeholder="{{ __('Task title') }}"
                                        required
                                    >
                                    @error('editingTaskTitle')
                                        <p class="text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                                        <div class="flex flex-col gap-1 sm:w-48">
                                            <select
                                                wire:model.defer="editingTaskPriority"
                                                class="rounded-lg border border-neutral-300 bg-neutral-50 px-2 py-2 text-xs font-medium text-neutral-700 transition dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                                            >
                                                @foreach (\App\Models\Task::PRIORITIES as $priority)
                                                    <option value="{{ $priority }}">{{ $priorityLabels[$priority] }}</option>
                                                @endforeach
                                            </select>
                                            @error('editingTaskPriority')
                                                <p class="text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="flex flex-1 justify-end gap-2">
                                            <button
                                                type="button"
                                                wire:click="cancelEditingBacklogTask"
                                                class="mouse-pointer rounded-lg border border-neutral-300 px-3 py-2 text-xs font-medium text-neutral-600 transition hover:border-neutral-400 hover:text-neutral-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-400 dark:border-neutral-600 dark:text-neutral-300 dark:hover:border-neutral-500"
                                            >
                                                {{ __('Cancel') }}
                                            </button>
                                            <button
                                                type="submit"
                                                class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-sky-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500"
                                            >
                                                {{ __('Save') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="flex w-full items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <button
                                            type="button"
                                            wire:click="toggleDone({{ $task->id }})"
                                            class="mouse-pointer flex size-7 items-center justify-center rounded-full border {{ $task->status === \App\Models\Task::STATUS_DONE ? 'border-green-500 bg-green-500 text-white' : 'border-neutral-300 text-neutral-500 dark:border-neutral-600' }}"
                                            title="{{ __('Toggle status') }}"
                                        >
                                            @if ($task->status === \App\Models\Task::STATUS_DONE)
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" aria-hidden="true" focusable="false">
                                                    <path fill="currentColor" d="M9.55 16.45 5.4 12.3l1.4-1.4 2.75 2.75 7.1-7.1 1.4 1.4Z"/>
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" aria-hidden="true" focusable="false">
                                                    <path fill="currentColor" d="M12 20a8 8 0 1 1 0-16 8 8 0 0 1 0 16Zm0-2a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z"/>
                                                </svg>
                                            @endif
                                        </button>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-medium leading-tight {{ $task->status === \App\Models\Task::STATUS_DONE ? 'text-neutral-400 line-through' : '' }}">
                                                    {{ $task->title }}
                                                </p>
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $priorityBadgeClasses[$task->priority] ?? 'bg-neutral-100 text-neutral-700 ring-1 ring-neutral-300' }}">
                                                    {{ $priorityLabels[$task->priority] ?? ucfirst($task->priority) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                {{ __('Created :date', ['date' => $task->created_at->format('d M')]) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            wire:click="planTask({{ $task->id }})"
                                            class="mouse-pointer rounded-lg bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700 transition hover:bg-sky-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-400 dark:bg-sky-900/40 dark:text-sky-300 dark:hover:bg-sky-900/60"
                                        >
                                            {{ __('Plan for :date', ['date' => $selectedDateCarbon->format('D')]) }}
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="startEditingBacklogTask({{ $task->id }})"
                                            class="mouse-pointer rounded-lg bg-amber-100 p-2 text-amber-700 transition hover:bg-amber-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-400 dark:bg-amber-900/30 dark:text-amber-200 dark:hover:bg-amber-900/50"
                                            title="{{ __('Edit task') }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4 mouse-pointer" aria-hidden="true">
                                              <path fill="currentColor" d="M21 12a1 1 0 0 0-1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h6a1 1 0 0 0 0-2H5a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-6a1 1 0 0 0-1-1m-15 .76V17a1 1 0 0 0 1 1h4.24a1 1 0 0 0 .71-.29l6.92-6.93L21.71 8a1 1 0 0 0 0-1.42l-4.24-4.29a1 1 0 0 0-1.42 0l-2.82 2.83l-6.94 6.93a1 1 0 0 0-.29.71m10.76-8.35l2.83 2.83l-1.42 1.42l-2.83-2.83ZM8 13.17l5.93-5.93l2.83 2.83L10.83 16H8Z"/>
                                            </svg>
                                            <span class="sr-only">{{ __('Edit') }}</span>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="deleteTask({{ $task->id }})"
                                            class="mouse-pointer rounded-lg bg-rose-100 p-2 text-rose-700 transition hover:bg-rose-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-400 dark:bg-rose-900/30 dark:text-rose-200 dark:hover:bg-rose-900/50"
                                            title="{{ __('Delete task') }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" aria-hidden="true">
                                                <path fill="currentColor" d="M9 3h6a1 1 0 0 1 1 1v1h4v2h-1v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7H4V5h4V4a1 1 0 0 1 1-1Zm1 4v11h2V7Zm4 0v11h2V7Z"/>
                                            </svg>
                                            <span class="sr-only">{{ __('Delete') }}</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="rounded-lg bg-neutral-50 p-4 text-sm text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
                            {{ __('All clear! Capture the next idea with "n".') }}
                        </p>
                    @endforelse
                </div>
            </div>
        </article>

        <article class="flex flex-col rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <header class="flex items-center justify-between border-b border-neutral-200 px-4 py-3 dark:border-neutral-800">
                <flux:heading size="sm">{{ __('Plan for :date', ['date' => $selectedDateCarbon->isoFormat('dddd, D MMM')]) }}</flux:heading>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('Focus-first list - drag handles appear on hover') }}</span>
            </header>

            <div class="flex flex-1 flex-col gap-3 p-4">
                <form wire:submit.prevent="createPlannedTask" class="flex items-center gap-2">
                    <input
                        type="text"
                        required
                        wire:model.defer="newPlannedTaskTitle"
                        placeholder="{{ __('What is the next most valuable task?') }}"
                        class="w-full rounded-lg border border-neutral-300 bg-transparent px-3 py-2 text-sm focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:border-neutral-700"
                    >
                    <select
                        wire:model.defer="newTaskPriority"
                        class="rounded-lg border border-neutral-300 bg-neutral-50 px-2 py-2 text-xs font-medium text-neutral-700 transition dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                    >
                        @foreach (\App\Models\Task::PRIORITIES as $priority)
                            <option value="{{ $priority }}">{{ $priorityLabels[$priority] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-sky-600 px-3 py-2 text-sm font-medium text-white shadow hover:bg-sky-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500">
                        {{ __('Plan') }}
                    </button>
                </form>

                <div class="flex flex-col gap-2 overflow-y-auto pb-2">
                    @forelse ($plannedTasks as $task)
                        <div class="flex items-start gap-3 rounded-xl border border-neutral-200 bg-white p-3 shadow-sm transition hover:border-primary-200 dark:border-neutral-700 dark:bg-neutral-800">
                            <button
                                type="button"
                                wire:click="toggleDone({{ $task->id }})"
                                class="mouse-pointer mt-1 flex size-7 items-center justify-center rounded-full border {{ $task->status === \App\Models\Task::STATUS_DONE ? 'border-green-500 bg-green-500 text-white' : 'border-neutral-300 text-neutral-500 dark:border-neutral-600' }}"
                            >
                                @if ($task->status === \App\Models\Task::STATUS_DONE)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" aria-hidden="true" focusable="false">
                                        <path fill="currentColor" d="M9.55 16.45 5.4 12.3l1.4-1.4 2.75 2.75 7.1-7.1 1.4 1.4Z"/>
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" aria-hidden="true" focusable="false">
                                        <path fill="currentColor" d="M12 20a8 8 0 1 1 0-16 8 8 0 0 1 0 16Zm0-2a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z"/>
                                    </svg>
                                @endif
                            </button>
                            <div class="flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium leading-tight {{ $task->status === \App\Models\Task::STATUS_DONE ? 'text-neutral-400 line-through' : '' }}">
                                        {{ $task->title }}
                                    </p>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $priorityBadgeClasses[$task->priority] ?? 'bg-neutral-100 text-neutral-700 ring-1 ring-neutral-300' }}">
                                        {{ $priorityLabels[$task->priority] ?? ucfirst($task->priority) }}
                                    </span>
                                    @if ($task->project)
                                        <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-[10px] font-semibold text-neutral-600 dark:bg-neutral-700 dark:text-neutral-200">
                                            {{ $task->project->name }}
                                        </span>
                                    @endif
                                </div>
                                @if ($task->description)
                                    <p class="text-sm text-neutral-500 dark:text-neutral-300">{{ $task->description }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <button
                                    type="button"
                                    wire:click="moveTaskToBacklog({{ $task->id }})"
                                    class="mouse-pointer rounded-lg border border-neutral-200 px-2 py-1 text-xs text-neutral-500 transition hover:border-neutral-300 hover:text-neutral-700 dark:border-neutral-700 dark:text-neutral-300"
                                >
                                    {{ __('Backlog') }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg bg-neutral-50 p-4 text-sm text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
                            {{ __('No tasks planned yet. Send one from backlog or create a new focus item.') }}
                        </p>
                    @endforelse
                </div>
            </div>
        </article>
    </section>
</div>
