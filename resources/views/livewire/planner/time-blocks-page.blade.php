@php
    $timeBlocks = $timeBlocks ?? collect();
    $tasks = $tasks ?? collect();
    $selectedDateString = $selectedDate ?? now()->toDateString();
    $selectedDateCarbon = \Illuminate\Support\Carbon::parse($selectedDateString);
@endphp

<div class="flex h-full flex-col gap-6">
    <header class="flex flex-wrap items-center gap-4">
        <flux:heading size="xl">{{ __('Time Blocks') }}</flux:heading>

        <div class="flex items-center gap-3">
            <label class="text-sm font-medium text-neutral-500 dark:text-neutral-400" for="time-block-date">
                {{ __('Tanggal') }}
            </label>
            <input
                id="time-block-date"
                type="date"
                wire:model.live="selectedDate"
                class="rounded-lg border border-neutral-300 bg-transparent px-3 py-2 text-sm dark:border-neutral-700"
            >
        </div>

        <div class="ml-auto text-xs text-neutral-500 dark:text-neutral-400">
            {{ __('Blok waktu membantu kamu menjaga fokus tiap sesi.') }}
        </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="flex flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <flux:heading size="sm">{{ __('Buat Blok Baru') }}</flux:heading>

            <form wire:submit.prevent="createTimeBlock" class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <label class="space-y-1">
                    <span class="block text-xs text-neutral-500 dark:text-neutral-300">{{ __('Mulai') }}</span>
                    <input type="time" wire:model.defer="newTimeBlockStart" class="w-full rounded-lg border border-neutral-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-neutral-900" min="05:00" max="23:45" step="900">
                </label>

                <label class="space-y-1">
                    <span class="block text-xs text-neutral-500 dark:text-neutral-300">{{ __('Selesai') }}</span>
                    <input type="time" wire:model.defer="newTimeBlockEnd" class="w-full rounded-lg border border-neutral-300 bg-white px-2 py-2 dark:border-neutral-700 dark:bg-neutral-900" min="05:15" max="24:00" step="900">
                </label>

                <label class="sm:col-span-2 space-y-1">
                    <span class="block text-xs text-neutral-500 dark:text-neutral-300">{{ __('Tautkan ke tugas (opsional)') }}</span>
                    <select wire:model.defer="newTimeBlockTaskId" class="w-full rounded-lg border border-neutral-300 bg-white px-2 py-2 text-sm text-neutral-700 transition focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:focus:border-primary-500 dark:focus:ring-primary-500">
                        <option value="">{{ __('Tidak ada') }}</option>
                        @foreach ($tasks as $task)
                            <option value="{{ $task->id }}">
                                {{ $task->title }} @if($task->planned_date) - {{ $task->planned_date->format('d M') }} @endif
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="sm:col-span-2 space-y-1">
                    <span class="block text-xs text-neutral-500 dark:text-neutral-300">{{ __('Catatan') }}</span>
                    <input type="text" wire:model.defer="newTimeBlockNote" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 dark:border-neutral-700 dark:bg-neutral-900" placeholder="{{ __('Contoh: Deep work sprint...') }}">
                </label>

                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-sky-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500">
                        {{ __('Simpan Blok') }}
                    </button>
                </div>
            </form>

        <div class="text-xs text-neutral-500 dark:text-neutral-400">
            {{ __('Gunakan interval 15 menit agar jadwal tetap rapih.') }}
        </div>
    </section>

    <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <flux:heading size="sm" class="mb-4">{{ __('Jadwal :date', ['date' => $selectedDateCarbon->isoFormat('dddd, D MMM')]) }}</flux:heading>

        <div class="flex flex-col gap-2">
            @forelse ($timeBlocks as $block)
                <div class="flex items-start justify-between gap-3 rounded-xl border border-neutral-200 bg-white p-3 text-sm shadow-sm transition hover:border-primary-200 dark:border-neutral-700 dark:bg-neutral-800">
                    <div class="space-y-1">
                        <p class="font-semibold text-neutral-700 dark:text-neutral-200">
                            {{ $block->start_at->format('H:i') }} - {{ $block->end_at->format('H:i') }}
                        </p>
                        @if ($block->task)
                            <p class="text-xs font-medium text-neutral-600 dark:text-neutral-300">
                                {{ __('Tugas') }}: {{ $block->task->title }}
                            </p>
                        @endif
                        @if ($block->note)
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $block->note }}
                            </p>
                        @elseif (! $block->task)
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ __('Tanpa catatan') }}
                            </p>
                        @endif
                    </div>
                    <button
                        type="button"
                        wire:click="deleteTimeBlock({{ $block->id }})"
                        class="mouse-pointer rounded-lg bg-rose-100 px-2 py-1 text-xs font-medium text-rose-700 transition hover:bg-rose-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-400 dark:bg-rose-900/30 dark:text-rose-200 dark:hover:bg-rose-900/50"
                    >
                        {{ __('Hapus') }}
                    </button>
                </div>
            @empty
                <p class="rounded-lg bg-neutral-50 p-4 text-sm text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
                    {{ __('Belum ada blok di tanggal ini. Buat blok pertama untuk menjaga fokusmu.') }}
                </p>
            @endforelse
        </div>
    </section>
</div>
</div>
