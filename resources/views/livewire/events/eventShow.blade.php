<?php
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public $name;

    public function getEventProperty()
    {
        $response = Http::withToken(session('token'))->get(env('API_BASE_URL') . '/events/' . $this->name);
        return $response->json('data');
    }
};
?>

<div class="space-y-8">
    {{-- Header --}}
    <div>
        <div class="flex justify-between items-center">
            <div>
                <flux:heading size="xl" level="1" class="font-semibold text-gray-900 dark:text-white">
                    {{ $this->event['event']['name'] ?? 'Event Detail' }}
                </flux:heading>
                <flux:subheading size="lg" class="text-gray-500 dark:text-gray-400">
                    View or manage event {{ $this->event['event']['description'] }}
                </flux:subheading>
            </div>

            <flux:button as-a :href="route('scan.admin', $this->event['event']['slug'])" icon="qr-code" variant="primary"
                class="mt-4" wire:click="$emit('goBack')">
                Scan Check In
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800
    rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-5">
            <div class="flex-shrink-0">
                <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300">
                    <flux:icon name="map-pin" class="w-8 h-8" />
                </div>
            </div>

            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">
                    Location
                </span>
                <p class="text-base font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                    {{ $this->event['event']['location'] ?? '-' }}
                </p>
            </div>
        </div>

        {{-- Date Widget --}}
        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800
    rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-5">

            <div class="flex-shrink-0">
                <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-300">
                    <flux:icon name="calendar" class="w-8 h-8" />
                </div>
            </div>

            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">
                    Date
                </span>
                <p class="text-base font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                    {{ \Carbon\Carbon::parse($this->event['event']['start_date'])->format('d M Y') }}
                    –
                    {{ \Carbon\Carbon::parse($this->event['event']['end_date'])->format('d M Y') }}
                </p>
            </div>
        </div>

        {{-- Time Widget --}}
        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800
    rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-5">

            <div class="flex-shrink-0">
                <div class="p-4 rounded-2xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300">
                    <flux:icon name="clock" class="w-8 h-8" />
                </div>
            </div>

            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">
                    Time
                </span>
                <p class="text-base font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                    {{ $this->event['event']['start_time'] }} – {{ $this->event['event']['end_time'] }}
                </p>
            </div>
        </div>

        {{-- Status Widget --}}
        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800
    rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-5">

            <div class="flex-shrink-0">
                <div class="p-4 rounded-2xl
            @class([
                'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300' =>
                    $this->event['event']['status'] === 'upcoming',
                'bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-300' =>
                    $this->event['event']['status'] === 'ongoing',
                'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400' =>
                    $this->event['event']['status'] === 'finished',
            ])
        ">
                    <flux:icon name="check-badge" class="w-8 h-8" />
                </div>
            </div>

            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">
                    Status
                </span>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                    {{ Str::title(str_replace('_', ' ', $this->event['event']['status'] ?? 'Unknown')) }}
                </p>
            </div>
        </div>

    </div>


    {{-- Guests Table --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 p-6">
        <div class="flex justify-between items-center mb-4">
            <flux:heading size="lg">Guest List</flux:heading>
            <flux:input size="sm" placeholder="Filter by..." class="w-md" />
            <flux:button size="sm" variant="primary">
                + Add Guest
            </flux:button>
        </div>

        @if (!empty($this->event['guests']))
            <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800">
                <table class="min-w-full text-sm text-left text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-zinc-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Phone</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->event['guests'] as $guest)
                            <tr
                                class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
                                <td class="px-6 py-3 font-medium text-gray-900 dark:text-gray-100">
                                    {{ $guest['name'] }}
                                </td>
                                <td class="px-6 py-3">{{ $guest['email'] }}</td>
                                <td class="px-6 py-3">{{ $guest['phone'] }}</td>
                                <td class="px-6 py-3">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium
                                        @class([
                                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' =>
                                                $guest['status'] === 'checked_in',
                                            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' =>
                                                $guest['status'] === 'invited',
                                            'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400' =>
                                                $guest['status'] === 'cancelled',
                                        ])
                                    ">
                                        {{ Str::title(str_replace('_', ' ', $guest['status'])) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right flex justify-end gap-2">
                                    <flux:button size="sm" variant="ghost">
                                        View QR
                                    </flux:button>
                                    <flux:button size="sm" variant="ghost">
                                        Edit
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-10 text-gray-400 dark:text-gray-500">
                No guests found for this event.
            </div>
        @endif
    </div>
</div>
