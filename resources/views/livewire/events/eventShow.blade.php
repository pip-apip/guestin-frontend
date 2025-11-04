<?php

use Flux\Flux;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public $name;
    public $guests;
    public $date = '';
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
    public $searchStatus = '';
    public $filterSort = 'name';
    public $filterOrder = 'asc';

    public function selectDay($date){
        $this->date = $date;
        $this->loadGuests();
    }

    public function mount()
    {
        $this->date = today()->format('Y-m-d') ;
        $this->loadGuests();
    }

    public function getEventProperty()
    {
        $response = Http::withToken(session('token'))->get(env('API_BASE_URL') . '/events/' . $this->name);
        $this->guestData['events_id'] = $response->json()['data']['id'] ?? null;
        $data = $response->json()['data'];
        return  $data ?? [];
    }
    public function dateRange(){

        if (!isset($this->event['event']['start_date'], $this->event['event']['end_date'])) {
            return [];
        }

        $start = $this->event['event']['start_date'];
        $end   = $this->event['event']['end_date'];

        $period = CarbonPeriod::create($start, $end);

        return collect($period)->map->toDateString()->toArray();
    }

    public function loadGuests()
    {
        $params = [
            'events_id' => $this->event['event']['id'] ?? null,
            'status' => $this->searchStatus,
            'search' => $this->searchQuery,
            'sort_by' => $this->filterSort,
            'order' => $this->filterOrder,
            'filter_date' => $this->date
        ];
        try {
            $response = Http::get(env('API_BASE_URL') . '/guests?', $params);
            $result = $response->json();
            $this->guests = $result['data'] ?? [];
        } catch (\Exception $e) {
            $this->guests = [];
        }
    }

    public function updatedDateQuery()
    {
        $this->loadGuests();
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

            if ($response['message'] === 'success') {
                session()->flash('success', $response['message'] ?? 'Guest added successfully.');
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
                    Event Detail
                </flux:heading>
                <flux:subheading class="text-xs w-full line-clamp-2 md:text-sm text-gray-500 dark:text-gray-400">
                    View or manage event details
                </flux:subheading>
            </div>

            <flux:button as-a :href="route('scan.admin', $this->event['event']['slug'])" icon="qr-code" variant="primary" class="mt-4" wire:click="$emit('goBack')">
                Scan Check In
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-5 gap-3">
        <div class="col-span-4 grid grid-rows-[auto_1fr] gap-3">
            <livewire:components.widget-event :data="$this->event['widgets']" />

            <div class="block p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md dark:bg-zinc-900 dark:border-zinc-800 transition-all">
                <!-- Event Name -->
                <h5 class="text-2xl font-bold flex items-center justify-between text-gray-900 dark:text-white">
                    {{ $this->event['event']['name'] }}
                    <flux:badge :color="
                        match($this->event['event']['status']) {
                            'upcoming' => 'blue',
                            'ongoing' => 'green',
                            'completed' => 'gray',
                            'cancelled' => 'red',
                            default => 'gray',
                        }
                    " class="text-xs font-medium ml-2">
                        {{ ucfirst($this->event['event']['status']) }}
                    </flux:badge>
                </h5>


                <!-- Event Info Bar -->
                <div class="flex flex-wrap items-center text-sm text-gray-400 dark:text-gray-40 mt-1 mb-4 gap-4">
                    <!-- Dates -->
                    <div class="flex items-center gap-1">
                        <flux:icon name="calendar" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                        <span>
                            {{ Carbon::parse($this->event['event']['start_date'])->format('D, d F Y') }}
                            -
                            {{ Carbon::parse($this->event['event']['end_date'])->format('D, d F Y') }}
                        </span>
                    </div>

                    <!-- Times -->
                    <div class="flex items-center gap-1">
                        <flux:icon name="clock" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                        <span>
                            {{ $this->event['event']['start_time'] }} - {{ $this->event['event']['end_time'] }}
                        </span>
                    </div>

                    <!-- Location -->
                    <div class="flex items-center gap-1">
                        <flux:icon name="map-pin" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                        <span>{{ $this->event['event']['location'] }}</span>
                    </div>

                </div>

                <!-- Event Description -->
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ $this->event['event']['description'] }}
                </p>
            </div>

        </div>

        <div class="flex flex-col items-center justify-center gap-3 h-full p-6 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm hover:shadow-md transition">

            <!-- Days Remaining / Total Days -->
            @php
            $today = Carbon::today();
            $start = Carbon::parse($this->event['event']['start_date']);
            $end = Carbon::parse($this->event['event']['end_date']);

            if ($today->lt($start)) {
            // Event upcoming
            $daysRemaining = $start->diffInDays($today) + 1; // termasuk hari ini
            } elseif ($today->between($start, $end)) {
            // Event ongoing
            $daysRemaining = $today->diffInDays($end) + 1; // sisa hari
            } else {
            // Event completed
            $daysRemaining = $start->diffInDays($end) + 1; // total hari event
            }
            @endphp

            <h5 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                {{ $daysRemaining }}
            </h5>
            <p class="text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                {{ $this->event['event']['status'] === 'upcoming' ? 'Days Remaining' : 'Total Days' }}
            </p>

            <!-- Progress Bar -->
            @php
            $totalDays = $start->diffInDays($end) + 1;

            if ($this->event['event']['status'] === 'upcoming') {
            $progress = 0;
            } elseif ($this->event['event']['status'] === 'ongoing') {
            $daysPassed = $today->diffInDays($end) + 1; // hari sudah berjalan termasuk hari ini
            $progress = ($daysPassed / $totalDays) * 100;
            } else {
            $progress = 100;
            }
            @endphp

            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
            </div>
            <div class="flex w-full justify-between">
                <span class="text-xs font-medium text-blue-700 dark:text-white"></span>
                <span class="text-xs font-medium text-blue-700 dark:text-white">{{ $progress }}%</span>
            </div>
        </div>
    </div>

    {{-- Date Selector --}}
    <ul class="text-sm max-w-5xl mx-auto font-medium rounded-xl text-center text-gray-500 shadow-sm flex sm:justify-center dark:divide-gray-700 dark:text-gray-400 overflow-hidden">
        @foreach ($this->dateRange() as $date)
        <li class="flex-1">
            <button wire:click="selectDay('{{ $date }}')" class="w-full transition-colors duration-300 px-4 py-2 border border-gray-200 dark:border-zinc-700 text-black hover:border-gray-800 cursor-pointer focus:ring-4 dark:hover:text-white dark:bg-zinc-900 dark:hover:bg-zinc-700
                {{ $loop->first ? 'rounded-l-xl' : '' }}
                {{ $loop->last ? 'rounded-r-xl' : '' }}
                {{ $this->date === $date ? ' bg-black text-white' : ' dark:text-zinc-700 bg-white dark:bg-zinc-800' }}">
                Day {{ $loop->iteration }}
            </button>
        </li>
        @endforeach
    </ul>



    {{-- Guests Table --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 p-6">
        <div class="flex justify-between items-center mb-4">
            <flux:heading size="lg">Guest List </flux:heading>
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
                    <flux:input type="search" placeholder="Search guests..." wire:model.live.debounce.350ms="searchQuery" />
                </div>
                <div class="flex justify-between gap-4">
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Status</flux:label>
                    <flux:select wire:model.live="searchStatus" placeholder="Select Status ...">
                        <flux:select.option value="">All</flux:select.option>
                        <flux:select.option value="invited">Invited</flux:select.option>
                        <flux:select.option value="confirmed">Confirmed</flux:select.option>
                        <flux:select.option value="checked_in">Checked In</flux:select.option>
                        <flux:select.option value="canceled">Canceled</flux:select.option>
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
                    <tr wire:key="{{ $guest['id'] }}-{{ $this->date }}" class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-gray-100">
                            {{ $guest['name'] }}
                        </td>
                        <td class="px-6 py-3">{{ $guest['email'] }}</td>
                        <td class="px-6 py-3">{{ $guest['phone'] }}</td>
                        <td class="px-6 py-3">{{ $guest['organization'] ?? '-' }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
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
                                <flux:button class="text-sm" :disabled="$guest['qr_generated'] == null" variant="primary" size="sm">
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
                            <flux:button size="sm" variant="ghost" wire:click="edit({{ $guest['id'] }})">
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
            <flux:input label="Guest Name" placeholder="Type the name of the guest" wire:model.defer="guestData.name" />
            <flux:select label="Event" placeholder="Select event name" wire:model.defer="guestData.events_id" disabled>
                <flux:select.option value="{{ $this->event['event']['id'] }}" selected>
                    {{ $this->event['event']['name'] }}</flux:select.option>
            </flux:select>
            <flux:input type="email" label="Email Guest" placeholder="Type the email of the guest" wire:model.defer="guestData.email" />
            <flux:input label="Phone Guest" placeholder="Type the phone of the guest" wire:model.defer="guestData.phone" />
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
