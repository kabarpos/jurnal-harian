<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    <flux:navlist.item icon="clock" :href="route('time-blocks')" :current="request()->routeIs('time-blocks')" wire:navigate>{{ __('Time Blocks') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>

            <div x-data class="hidden lg:flex items-center justify-end px-2">
                <button
                    type="button"
                    @click="$flux.appearance = $flux.dark ? 'light' : 'dark'"
                    :aria-label="$flux.dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'"
                    class="mouse-pointer inline-flex h-10 w-10 items-center justify-center rounded-full border border-transparent bg-neutral-100 text-neutral-700 transition hover:bg-neutral-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-400 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700"
                >
                    <svg x-show="$flux.dark" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-5" aria-hidden="true">
                        <path fill="currentColor" d="M12 18a6 6 0 1 1 6-6a6 6 0 0 1-6 6m0 2a1 1 0 0 1 1 1v1h-2v-1a1 1 0 0 1 1-1M5.64 17l-.7.71l-1.42-1.42l.7-.7a1 1 0 0 1 1.42 1.41M4 12a1 1 0 0 1-1 1H2v-2h1a1 1 0 0 1 1 1m1.64-7.71l-1.42-1.42l1.42-1.41l1.41 1.41a1 1 0 1 1-1.41 1.42M13 2v1a1 1 0 0 1-2 0V2Zm6.36 3.29a1 1 0 0 1-1.41 0a1 1 0 0 1 0-1.42l1.41-1.41l1.42 1.41ZM22 11v2h-1a1 1 0 0 1-1-1a1 1 0 0 1 1-1Zm-1.5 5.29l-1.42 1.42l-1.41-1.42a1 1 0 0 1 1.41-1.41Z"/>
                    </svg>
                    <svg x-show="!$flux.dark" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-5" aria-hidden="true">
                        <path fill="currentColor" d="M10 7a7.002 7.002 0 0 1 6.938 6.312A5 5 0 0 1 9 9.5A4.5 4.5 0 0 1 10 7m1.85-5A9 9 0 1 0 21 13.15a1 1 0 0 0-1.18-1.16A7 7 0 0 1 12 4.18A1 1 0 0 0 11.85 2"/>
                    </svg>
                    <span class="sr-only">{{ __('Toggle dark mode') }}</span>
                </button>
            </div>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                    data-test="sidebar-menu-button"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <div x-data class="flex items-center">
                <button
                    type="button"
                    @click="$flux.appearance = $flux.dark ? 'light' : 'dark'"
                    :aria-label="$flux.dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'"
                    class="mouse-pointer inline-flex h-10 w-10 items-center justify-center rounded-full border border-transparent bg-neutral-100 text-neutral-700 transition hover:bg-neutral-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-400 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700"
                >
                    <svg x-show="$flux.dark" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-5" aria-hidden="true">
                        <path fill="currentColor" d="M12 18a6 6 0 1 1 6-6a6 6 0 0 1-6 6m0 2a1 1 0 0 1 1 1v1h-2v-1a1 1 0 0 1 1-1M5.64 17l-.7.71l-1.42-1.42l.7-.7a1 1 0 0 1 1.42 1.41M4 12a1 1 0 0 1-1 1H2v-2h1a1 1 0 0 1 1 1m1.64-7.71l-1.42-1.42l1.42-1.41l1.41 1.41a1 1 0 1 1-1.41 1.42M13 2v1a1 1 0 0 1-2 0V2Zm6.36 3.29a1 1 0 0 1-1.41 0a1 1 0 0 1 0-1.42l1.41-1.41l1.42 1.41ZM22 11v2h-1a1 1 0 0 1-1-1a1 1 0 0 1 1-1Zm-1.5 5.29l-1.42 1.42l-1.41-1.42a1 1 0 0 1 1.41-1.41Z"/>
                    </svg>
                    <svg x-show="!$flux.dark" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-5" aria-hidden="true">
                        <path fill="currentColor" d="M10 7a7.002 7.002 0 0 1 6.938 6.312A5 5 0 0 1 9 9.5A4.5 4.5 0 0 1 10 7m1.85-5A9 9 0 1 0 21 13.15a1 1 0 0 0-1.18-1.16A7 7 0 0 1 12 4.18A1 1 0 0 0 11.85 2"/>
                    </svg>
                    <span class="sr-only">{{ __('Toggle dark mode') }}</span>
                </button>
            </div>

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
