<?php

use Livewire\Volt\Component;

new class extends component{

}

?>

<div>
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 h-fit">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-4">
            <div class="flex-shrink-0 p-3  dark:bg-zinc-800 rounded-xl">
                <flux:icon name="users" class="w-8 h-8 text-blue-500" />
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs tracking-wide text-gray-500 dark:text-gray-400 font-medium">
                    Total Guests
                </span>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                    50
                </p>
            </div>
        </div>

        {{-- Date Widget --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-4">
            <div class="flex-shrink-0 p-3  dark:bg-zinc-800 rounded-xl">
                <flux:icon name="check-badge" class="w-8 h-8 text-blue-500" />
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs tracking-wide text-gray-500 dark:text-gray-400 font-medium">
                    Total Confirmed
                </span>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                    1 / 3
                </p>
            </div>
        </div>

        {{-- Time Widget --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-4">
            <div class="flex-shrink-0 p-3  dark:bg-zinc-800 rounded-xl">
                <flux:icon name="check" class="w-8 h-8 text-blue-500" />
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs tracking-wide text-gray-500 dark:text-gray-400 font-medium">
                    Checked In Today
                </span>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                    0
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-4">
            <div class="flex-shrink-0 p-3  dark:bg-zinc-800 rounded-xl">
                <flux:icon name="users" class="w-8 h-8 text-blue-500" />
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs tracking-wide text-gray-500 dark:text-gray-400 font-medium">
                    Not Checked In Today
                </span>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                    1
                </p>
            </div>
        </div>

    </div>

</div>
