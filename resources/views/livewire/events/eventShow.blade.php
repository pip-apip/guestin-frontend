<?php

use Flux\Flux;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public $name;
    public $guests;
    public $addModal = true;
    public $guestData = [
        'id' => '',
        'name' => '',
        'email' => '',
        'phone' => '',
        'organization' => '',
        'event_name' => '',
        'status' => '',
    ];

    public $searchQuery = '';
    public $searchStatus = 'invited';
    public $filterSort = 'name';
    public $filterOrder = 'asc';

    public function mount()
    {
        $this->loadGuests();
    }

    public function getEventProperty()
    {
        $response = Http::withToken(session('token'))->get(env('API_BASE_URL') . '/events/' . $this->name);
        $this->guestData['events_id'] = $response->json()['data']['id'] ?? null;
        return $response->json()['data'] ?? [];
    }

    public function loadGuests()
    {
        $params = [
            'events_id' => $this->event['event']['id'] ?? null,
            'status' => $this->searchStatus,
            'search' => $this->searchQuery,
            'sort_by' => $this->filterSort,
            'order' => $this->filterOrder,
        ];
        try {
            $response = Http::get(env('API_BASE_URL') . '/guests?', $params);
            $result = $response->json();
            $this->guests = '';
            $this->guests = $result['data'] ?? [];
        } catch (\Exception $e) {
            $this->guests = [];
        }
    }

    public function updatedSearchQuery()
    {
        $this->loadGuests();
    }

    public function updatedSearchStatus()
    {
        $this->loadGuests();
    }

    public function updatedFilterSort()
    {
        $this->loadGuests();
    }

    public function updatedFilterOrder()
    {
        $this->loadGuests();
    }

    public function save()
    {
        if ($this->addModal) {
            $this->guestData['status'] = 'invited';
        }
        $this->guestData['events_id'] = $this->event['event']['id'] ?? null;
        try {
            $response = Http::withToken(session('token'))
                ->post(env('API_BASE_URL') . '/guests', $this->guestData)
                ->json();
            session()->flash('success', $response['message'] ?? 'Guest added successfully.');

            if ($response['message'] === 'success') {
                // $this->getData();
                Flux::modals()->close();
                $this->guestData = [
                    'id' => '',
                    'name' => '',
                    'email' => '',
                    'phone' => '',
                    'organization' => '',
                    'events_id' => '',
                    'event_name' => '',
                    'status' => '',
                ];
                Toaster::success('Guest added successfully.');
                $this->loadGuests();
            } else {
                throw new \Exception($response['error'] ?? 'Failed to add guest.');
            }
        } catch (\Exception $e) {
            Toaster::error($e->getMessage() ?? 'Failed to add guest.');
        }
        Flux::modals()->close();
    }

    public function edit($guestId)
    {
        try {
            $response = Http::withToken(session('token'))
                ->get(env('API_BASE_URL') . '/guests/' . $guestId)
                ->json();
            // dd($response['data']['guest']['name']);
            $this->guestData['id'] = $response['data']['guest']['id'] ?? '';
            $this->guestData['name'] = $response['data']['guest']['name'] ?? '';
            $this->guestData['email'] = $response['data']['guest']['email'] ?? '';
            $this->guestData['phone'] = $response['data']['guest']['phone'] ?? '';
            $this->guestData['organization'] = $response['data']['guest']['organization'] ?? '';
            $this->guestData['event_name'] = $response['data']['guest']['event_name'] ?? '';
            $this->guestData['status'] = $response['data']['guest']['status'] ?? '';

            $this->addModal = false;
            Flux::modal('form-guest-modal')->show();
        } catch (\Exception $th) {
            //throw $th;
        }
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
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-5">
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
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-5">

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
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-5">
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
            <flux:modal.trigger name="form-guest-modal">
                <flux:button size="sm" variant="primary">
                    + Add Guest
                </flux:button>
            </flux:modal.trigger>
        </div>
        <div class="justify-between mb-6 grid grid-flow-col grid-cols-3 gap-4 px-5 py">
            <div class="flex gap-4 col-span-2">
                <div class="flex justify-between gap-4">
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Search</flux:label>
                    <flux:input type="search" placeholder="Search guests..." wire:model.live="searchQuery" />
                </div>
                <div class="flex justify-between gap-4">
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Status</flux:label>
                    <flux:select wire:model.live="searchStatus" placeholder="Select Status ...">
                        <flux:select.option value="invited">Invited</flux:select.option>
                        <flux:select.option value="confirmed">Confirmed</flux:select.option>
                        <flux:select.option value="canceled">Canceled</flux:select.option>
                        {{-- <flux:select.option value="canceled">Canceled</flux:select.option> --}}
                    </flux:select>
                </div>
            </div>
            <div class="flex justify-between gap-4">
                <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Sort</flux:label>
                <flux:select wire:model.live="filterSort" placeholder="Sort By">
                    <flux:select.option value="name">Name</flux:select.option>
                    <flux:select.option value="email">Email</flux:select.option>
                    <flux:select.option value="organization">Organization</flux:select.option>
                </flux:select>
                <flux:select wire:model.live="filterOrder" placeholder="Order">
                    <flux:select.option value="asc">ASC</flux:select.option>
                    <flux:select.option value="desc">DESC</flux:select.option>
                </flux:select>
            </div>
        </div>

        @if (!empty($this->guests) && count($this->guests) > 0)
            <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800">
                <table class="min-w-full text-sm text-left text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-zinc-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Phone</th>
                            <th class="px-6 py-3">Organization</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Notes</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->guests as $guest)
                            <tr wire:key="{{ $guest['id'] }}"
                                class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
                                <td class="px-6 py-3 font-medium text-gray-900 dark:text-gray-100">
                                    {{ $guest['name'] }}
                                </td>
                                <td class="px-6 py-3">{{ $guest['email'] }}</td>
                                <td class="px-6 py-3">{{ $guest['phone'] }}</td>
                                <td class="px-6 py-3">{{ $guest['organization'] ?? '-' }}</td>
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
                                <td class="px-6 py-3">{{ $guest['notes'] ?? '-' }}</td>
                                <td class="px-6 py-3 text-right flex justify-end gap-2">
                                    <flux:modal.trigger name="qr-modal-{{ $guest['id'] }}">
                                        @if ($guest['qr_generated'] !== null)
                                            <flux:button class="text-sm" :disabled="$guest['qr_generated'] == null"
                                                variant="primary" size="sm">
                                                Show QR
                                            </flux:button>
                                        @else
                                            <flux:tooltip content="QR Code not generated yet">
                                                <div>
                                                    <flux:button disabled size="sm">Show QR</flux:button>
                                                </div>
                                            </flux:tooltip>
                                        @endif

                                    </flux:modal.trigger>
                                    <flux:button size="sm" variant="ghost"
                                        wire:click="edit({{ $guest['id'] }})">
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

    <flux:modal name="form-guest-modal" class="md:w-200">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $addModal ? 'Add Guest' : 'Edit Guest' }}</flux:heading>
                <flux:text class="mt-2">Add a new guest to your list {{ $this->event['event']['id'] }}</flux:text>
            </div>
            <flux:input label="Guest Name" placeholder="Type the name of the guest"
                wire:model.defer="guestData.name" />
            <flux:select label="Event" placeholder="Select event name" wire:model.defer="guestData.events_id"
                disabled>
                <flux:select.option value="{{ $this->event['event']['id'] }}" selected>
                    {{ $this->event['event']['name'] }}</flux:select.option>
            </flux:select>
            <flux:input type="email" label="Email Guest" placeholder="Type the email of the guest"
                wire:model.defer="guestData.email" />
            <flux:input label="Phone Guest" placeholder="Type the phone of the guest"
                wire:model.defer="guestData.phone" />
            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary" wire:click="save">Save</flux:button>
            </div>
        </div>
    </flux:modal>

    @foreach ($this->guests as $modal)
        @if ($modal['qr_generated'])
            <flux:modal name="qr-modal-{{ $modal['id'] }}" class="md:w-96">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">QR Code for {{ $modal['name'] }} </flux:heading>
                        <flux:description class="mt-2">Scan this QR code at the event check-in.</flux:description>
                    </div>
                    <div class="flex items-center justify-center mb-4">
                        {!! $modal['qr_generated'] !!}
                    </div>
                    {{-- <flux:button variant="primary" wire:click="downloadQr({{ $modal['qr_generated'] }})">Download</flux:button> --}}
                </div>
            </flux:modal>
        @endif
    @endforeach
</div>
