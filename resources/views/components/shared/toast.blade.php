<div wire:key="toast-{{ now() }}">
    @if (session('success'))
        <x-toast type="success" :message="session('success')" />
    @endif

    @if (session('error'))
        <x-toast type="error" :message="session('error')" />
    @endif
</div>