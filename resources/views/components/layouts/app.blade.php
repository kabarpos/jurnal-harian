<x-layouts.app.header :title="$title ?? null">
    <flux:main class="w-full py-10">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts.app.header>
