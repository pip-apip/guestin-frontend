@props(['type' => 'success', 'message'])

@php
    $bg = match($type ?? 'success') {
        'error' => 'bg-red-500',
        'info' => 'bg-blue-500',
        'warning' => 'bg-yellow-500',
        default => 'bg-green-500',
    };
@endphp

<div
    x-data="{ show: false }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
    x-init="
        setTimeout(() => show = true, 10);
        setTimeout(() => show = false, 1000);
    "
    class="fixed top-6 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm {{ $bg }}"
>
    {{ $message }}
</div>
