@php
    $backlogTasks = $backlogTasks ?? collect();
    $plannedTasks = $plannedTasks ?? collect();
    $timeBlocks = $timeBlocks ?? collect();
    $selectedDateString = $selectedDate ?? now()->toDateString();
    $selectedDateCarbon = \Illuminate\Support\Carbon::parse($selectedDateString);
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

    <section class="grid gap-6 xl:grid-cols-3">
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
                        class="rounded-lg border border-neutral-300 bg-transparent px-2 py-2 text-xs uppercase tracking-wide dark:border-neutral-700"
                    >
                        <option value="p1">{{ __('P1') }}</option>
                        <option value="p2">{{ __('P2') }}</option>
                        <option value="p3">{{ __('P3') }}</option>
                        <option value="p4">{{ __('P4') }}</option>
                    </select>
                    <button type="submit" class="rounded-lg bg-primary-500 px-3 py-2 text-sm font-medium text-white shadow hover:bg-primary-600">
                        {{ __('Add') }}
                    </button>
                </form>

                <div class="flex flex-col gap-2 overflow-y-auto">
                    @forelse ($backlogTasks as $task)
                        <div
                            class="group flex items-center justify-between rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-2 text-sm shadow-sm transition hover:border-primary-200 hover:bg-white dark:border-neutral-700 dark:bg-neutral-800 dark:hover:border-primary-500"
                        >
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    wire:click="toggleDone({{ $task->id }})"
                                    class="flex size-7 items-center justify-center rounded-full border {{ $task->status === \App\Models\Task::STATUS_DONE ? 'border-green-500 bg-green-500 text-white' : 'border-neutral-300 text-neutral-500 dark:border-neutral-600' }}"
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
                                    <p class="font-medium {{ $task->status === \App\Models\Task::STATUS_DONE ? 'text-neutral-400 line-through' : '' }}">
                                        {{ $task->title }}
                                    </p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ strtoupper($task->priority) }} - {{ __('Created :date', ['date' => $task->created_at->format('d M')]) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="planTask({{ $task->id }})"
                                    class="rounded-lg border border-primary-200 px-2 py-1 text-xs font-medium text-primary-600 transition hover:bg-primary-50 dark:border-primary-800 dark:text-primary-300 dark:hover:bg-primary-950"
                                >
                                    {{ __('Plan for :date', ['date' => $selectedDateCarbon->format('D')]) }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg bg-neutral-50 p-4 text-sm text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
                            {{ __('All clear! Capture the next idea with "n".') }}
                        </p>
                    @endforelse
                </div>
            </div>
        </article>

        <article class="flex flex-col rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900 xl:col-span-2">
            <header class="flex items-center justify-between border-b border-neutral-200 px-4 py-3 dark:border-neutral-800">
                <flux:heading size="sm">{{ __('Plan for :date', ['date' => $selectedDateCarbon->isoFormat('dddd, D MMM')]) }}</flux:heading>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('Focus-first list - drag handles appear on hover') }}</span>
            </header>

            <div class="grid flex-1 grid-cols-1 gap-4 p-4 lg:grid-cols-5">
                <section class="col-span-3 flex flex-col gap-3">
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
                            class="rounded-lg border border-neutral-300 bg-transparent px-2 py-2 text-xs uppercase tracking-wide dark:border-neutral-700"
                        >
                            <option value="p1">{{ __('P1') }}</option>
                            <option value="p2">{{ __('P2') }}</option>
                            <option value="p3">{{ __('P3') }}</option>
                            <option value="p4">{{ __('P4') }}</option>
                        </select>
                        <button type="submit" class="rounded-lg bg-primary-500 px-3 py-2 text-sm font-medium text-white shadow hover:bg-primary-600">
                            {{ __('Plan') }}
                        </button>
                    </form>

                    <div class="flex flex-col gap-2 overflow-y-auto pb-2">
                        @forelse ($plannedTasks as $task)
                            <div class="flex items-start gap-3 rounded-xl border border-neutral-200 bg-white p-3 shadow-sm transition hover:border-primary-200 dark:border-neutral-700 dark:bg-neutral-800">
                                <button
                                    type="button"
                                    wire:click="toggleDone({{ $task->id }})"
                                    class="mt-1 flex size-7 items-center justify-center rounded-full border {{ $task->status === \App\Models\Task::STATUS_DONE ? 'border-green-500 bg-green-500 text-white' : 'border-neutral-300 text-neutral-500 dark:border-neutral-600' }}"
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
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide
                                            @class([
                                                'bg-red-100 text-red-700 ring-1 ring-red-300' => $task->priority === 'p1',
                                                'bg-blue-100 text-blue-700 ring-1 ring-blue-300' => $task->priority === 'p2',
                                                'bg-amber-100 text-amber-700 ring-1 ring-amber-300' => $task->priority === 'p3',
                                                'bg-slate-100 text-slate-700 ring-1 ring-slate-300' => $task->priority === 'p4',
                                            ])
                                        ">{{ strtoupper($task->priority) }}</span>
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
                                        class="rounded-lg border border-neutral-200 px-2 py-1 text-xs text-neutral-500 transition hover:border-neutral-300 hover:text-neutral-700 dark:border-neutral-700 dark:text-neutral-300"
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
                </section>

                <section class="col-span-2 flex flex-col gap-4">
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800">
                        <flux:heading size="xs" class="mb-3">{{ __('Time Blocks') }}</flux:heading>
                        <form wire:submit.prevent="createTimeBlock" class="grid grid-cols-2 gap-2 text-sm">
                            <label class="col-span-1 space-y-1">
                                <span class="block text-xs text-neutral-500 dark:text-neutral-300">{{ __('Start') }}</span>
                                <input type="time" wire:model.defer="newTimeBlockStart" class="w-full rounded-lg border border-neutral-300 bg-white px-2 py-1 dark:border-neutral-700 dark:bg-neutral-900" min="05:00" max="23:45" step="900">
                            </label>
                            <label class="col-span-1 space-y-1">
                                <span class="block text-xs text-neutral-500 dark:text-neutral-300">{{ __('End') }}</span>
                                <input type="time" wire:model.defer="newTimeBlockEnd" class="w-full rounded-lg border border-neutral-300 bg-white px-2 py-1 dark:border-neutral-700 dark:bg-neutral-900" min="05:15" max="24:00" step="900">
                            </label>
                            <label class="col-span-2 space-y-1">
                                <span class="block text-xs text-neutral-500 dark:text-neutral-300">{{ __('Link to task (optional)') }}</span>
                                <select wire:model.defer="newTimeBlockTaskId" class="w-full rounded-lg border border-neutral-300 bg-white px-2 py-1 dark:border-neutral-700 dark:bg-neutral-900">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach ($plannedTasks as $task)
                                        <option value="{{ $task->id }}">{{ $task->title }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="col-span-2 space-y-1">
                                <span class="block text-xs text-neutral-500 dark:text-neutral-300">{{ __('Note') }}</span>
                                <input type="text" wire:model.defer="newTimeBlockNote" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 dark:border-neutral-700 dark:bg-neutral-900" placeholder="{{ __('Optional note…') }}">
                            </label>
                            <div class="col-span-2 flex justify-end">
                                <button type="submit" class="rounded-lg bg-primary-500 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow hover:bg-primary-600">
                                    {{ __('Block time') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="flex flex-col gap-2 overflow-y-auto">
                        @forelse ($timeBlocks as $block)
                            <div class="flex items-center justify-between rounded-xl border border-neutral-200 bg-white p-3 text-sm shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                                <div>
                                    <p class="font-semibold text-neutral-700 dark:text-neutral-200">
                                        {{ $block->start_at->format('H:i') }}–{{ $block->end_at->format('H:i') }}
                                    </p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ $block->note ?: ($block->task?->title ?? __('Deep work')) }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    wire:click="deleteTimeBlock({{ $block->id }})"
                                    class="text-xs text-red-500 hover:text-red-600"
                                    title="{{ __('Delete block') }}"
                                >
                                    {{ __('Remove') }}
                                </button>
                            </div>
                        @empty
                            <p class="rounded-lg bg-neutral-50 p-4 text-sm text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
                                {{ __('No blocks yet. Map your deep work windows to stay on track.') }}
                            </p>
                        @endforelse
                    </div>
                </section>
            </div>
        </article>
    </section>
</div>
