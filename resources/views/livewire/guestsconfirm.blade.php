<?php

use Flux\Flux;
use Carbon\Carbon;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

new #[Layout('components.layouts.app-landing')] class extends Component {
    public $guestCode = '';
    public $guestData = [];
    public $url = '';
    public $cardNumber;

    public $start_date;
    public $end_date;
    public $selectedDate = [];
    public $finalSelectedDates = [];

    public function mount()
    {
        $this->guestCode = $this->getQueryStringParams();
        $this->guestData = $this->getGuestData();
        if ($this->cardNumber == 2) {
            foreach ($this->guestData['attendances'] as $attendance) {
                $this->finalSelectedDates[] = $attendance['attendance_date'];
            }
        }
    }

    public function getQueryStringParams()
    {
        $this->url = request()->url();
        $segments = explode('/', parse_url($this->url, PHP_URL_PATH));
        return end($segments);
    }

    public function getGuestData()
    {
        try {
            $response = Http::get(env('API_BASE_URL') . '/guests/confirm-guest/' . $this->guestCode);

            if ($response->successful()) {
                $data = $response->json()['data'];
                $this->start_date = Carbon::parse($data['event']['start_date']);
                $this->end_date = Carbon::parse($data['event']['end_date']);

                if (isset($data['guest']['qr_generated']) && $data['guest']['qr_generated'] !== null) {
                    $this->cardNumber = 2;
                } elseif (isset($data['guest']['qr_generated']) && $data['guest']['qr_generated'] === null) {
                    $this->cardNumber = 1;
                } else {
                    $this->cardNumber = 0;
                }
                return $data;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }

    public function getDateRangeProperty()
    {
        $dates = [];
        $current = $this->start_date->copy();

        while ($current->lte($this->end_date)) {
            $dates[] = $current->copy();
            $current->addDay();
        }
        \Log::info($this->start_date);
        return $dates;
    }

    public function toggleDate($date)
    {
        if (!is_array($this->selectedDate)) {
            $this->selectedDate = [];
        }

        if (in_array($date, $this->selectedDate)) {
            $this->selectedDate = array_values(array_diff($this->selectedDate, [$date]));
        } else {
            $this->selectedDate[] = $date;
        }
    }

    public function saveDates()
    {
        if (!is_array($this->selectedDate)) {
            $this->selectedDate = [];
        }

        $this->finalSelectedDates = $this->selectedDate;
        Flux::modals()->close();
        $this->selectedDate = [];
    }

    public function loadSavedDates()
    {
        $this->selectedDate = $this->finalSelectedDates;
    }

    public function saveAttendance()
    {
        $this->dispatch('verifyUser');
        try {
            $response = Http::post(env('API_BASE_URL') . '/guests/confirm/' . $this->guestCode, [
                'selected_date' => $this->finalSelectedDates,
                'status' => 'confirmed',
            ]);
            dd([
                'guest_code' => $this->guestCode,
                'attendance_dates' => $this->finalSelectedDates,
                'response' => $response->json(),
            ]);
            if ($response->successful()) {
                $this->dispatch('verifyUser');
            }
        } catch (\Exception $e) {
            \Log::error('Error saving attendance: ' . $e->getMessage());
        }
    }
}; ?>

<div class="w-full flex flex-col items-center justify-center">
    @if ($this->cardNumber == 0)
        <div
            class="flex flex-col items-center justify-center bg-white dark:bg-zinc-900 p-6 lg:px-14 rounded-xl shadow-xl w-full max-w-md aspect-auto">

            <!-- Heading -->
            <h1 class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100 mb-4 text-center">
                Guest Not Found
            </h1>

            <!-- Animated Icon Wrapper -->
            <div class="relative w-64 h-64 mb-4" id="iconWrapper">
                <!-- User Icon -->
                <svg id="userIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="0.5"
                    stroke="currentColor"
                    class="absolute inset-0 w-64 h-64 text-blue-500 transition-all duration-500 ease-in-out">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </div>

            <!-- Description -->
            <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                We could not find any guest information associated with the provided code. Please check the link or
                contact the event organizer for assistance.
            </p>
        </div>
    @elseif ($this->cardNumber == 1)
        <div
            class="flex flex-col items-center justify-center bg-white dark:bg-zinc-900 p-6 lg:px-14 rounded-xl shadow-xl w-full max-w-md aspect-auto">

            <!-- Heading -->
            <h1 class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100 mb-4 text-center">
                Confirm Your Attendance
            </h1>

            <!-- Animated Icon Wrapper -->
            <div class="relative w-64 h-64 mb-4" id="iconWrapper">
                <!-- User Icon -->
                <svg id="userIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="0.5" stroke="currentColor"
                    class="absolute inset-0 w-64 h-64 text-blue-500 transition-all duration-500 ease-in-out">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>

                <!-- Check Icon -->
                <svg id="checkIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="0.5" stroke="currentColor"
                    class="absolute inset-0 w-64 h-64 text-green-500 opacity-0 scale-0 transition-all duration-500 ease-in-out">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>

            <!-- Description -->
            <p class="text-center text-zinc-700 dark:text-zinc-200 mb-4 leading-relaxed">
                Hello <strong>{{ $guestData['guest']['name'] }}</strong>, we look forward to seeing you at the
                <strong>"{{ $guestData['event']['name'] }}"</strong> event, taking place from
                @if ($guestData['event']['start_date'] === $guestData['event']['end_date'])
                    <strong>{{ \Carbon\Carbon::parse($guestData['event']['start_date'])->format('D, d M Y') }}</strong>.
                @else
                    <strong>{{ \Carbon\Carbon::parse($guestData['event']['start_date'])->format('D, d M Y') }}</strong>
                    to
                    <strong>{{ \Carbon\Carbon::parse($guestData['event']['end_date'])->format('D, d M Y') }}</strong>
                @endif
                Please select your attendance date to confirm your participation in this event.
            </p>

            <flux:modal.trigger name="choose-date">
                <flux:button variant="primary" size="sm" wire:click="loadSavedDates"
                    class="flex x-6 py-2 {{ empty($this->finalSelectedDates) ? '' : 'bg-green-600 hover:bg-green-700 text-white' }}  rounded-lg shadow transition cursor-pointer">
                    Choose Attendance Date
                </flux:button>
            </flux:modal.trigger>

            @if (!empty($this->finalSelectedDates))
                <flux:separator variant="subtle" class="my-4" />
                <div class="mb-4 text-center">
                    <flux:heading size="lg">Summary Info</flux:heading>
                    <flux:text class="mt-2">You have selected the following attendance dates:</flux:text>
                </div>
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 mb-4">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                Day
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <tbody>
                        @foreach ($this->finalSelectedDates as $dateString)
                            @php
                                try {
                                    $date = \Carbon\Carbon::parse($dateString)->startOfDay();
                                    $startDate = \Carbon\Carbon::parse($this->start_date)->startOfDay();
                                    $dayNumber = $startDate->diffInDays($date) + 1;
                                } catch (\Exception $e) {
                                    continue; // Skip invalid dates
                                }
                            @endphp
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    Day {{ $dayNumber }}
                                </th>
                                <th scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $date->format('D, d M Y') }}
                                </th>
                            </tr>
                        @endforeach
                    </tbody>
                    </tbody>
                </table>
            @endif
        </div>

        @if (!empty($this->finalSelectedDates))
            <flux:button variant="primary" wire:click="saveAttendance"
                class="flex justify-center w-md py-2 bg-white dark:bg-zinc-900 hover:bg-zinc-700 text-white rounded-lg shadow-xl cursor-pointer transition my-4">
                Save Attendance
            </flux:button>
        @endif

        <flux:modal name="choose-date" class="md:w-200" @close="loadSavedDates">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Choose Attendance Dates</flux:heading>
                    <flux:text class="mt-2">Please select one or more attendance dates</flux:text>
                </div>

                <div class="grid grid-cols-3 lg:gap-4 gap-2">
                    @foreach ($this->dateRange as $date)
                        @php
                            $dateValue = $date->toDateString();
                            $isSelected = in_array($dateValue, $selectedDate);
                        @endphp

                        <div wire:click="toggleDate('{{ $dateValue }}')"
                            class="flex justify-center items-center cursor-pointer border rounded-lg p-3 text-center transition md:text-base text-xs
                        {{ $isSelected ? 'bg-gray-700/40 text-white/40 border-none' : 'hover:bg-gray-700/50 hover:text-white hover:border-gray-100' }}">

                            <div>{{ $date->format('D, d M Y') }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Save Button --}}
                <div class="flex justify-end mt-4">
                    <flux:button wire:click="saveDates" class="cursor-pointer">
                        Save Dates
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @elseif($this->cardNumber == 2)
        <div x-data="{ showSummary: false }" class="relative flex items-center justify-center w-full h-full bg-transparent">

            <!-- QR SECTION -->
            <div class="relative z-20 flex flex-col items-center justify-center bg-white dark:bg-zinc-900 p-6 lg:px-14 rounded-xl shadow-xl w-full max-w-md aspect-auto transition-all duration-500 ease-in-out"
                :class="showSummary ? '-translate-x-60' : 'translate-x-0'">
                <!-- Toggle Button -->
                <button @click="showSummary = !showSummary"
                    class="absolute flex items-center justify-center w-10 h-10
                bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800
                rounded-full shadow-md px-1
                text-zinc-600 dark:text-zinc-300 hover:text-zinc-800 dark:hover:text-white
                hover:shadow-lg transition-all duration-300 cursor-pointer"
                :class="showSummary ? '-right-15' : '-right-4.5'">
                    {{-- <span x-text="showSummary ? 'Hide Summary' : 'View Summary'"></span> --}}
                    <svg :class="{ 'rotate-180': showSummary }"
                        class="w-12 h-auto transform transition-transform duration-300" fill=""
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>



                <!-- Content -->
                <h1 class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100 mb-4 text-center">
                    Welcome, <strong>{{ $guestData['guest']['name'] }}</strong>
                </h1>

                <div class="flex items-center justify-center mb-4">
                    {!! $guestData['guest']['qr_generated'] !!}
                </div>

                <p class="text-center text-zinc-700 dark:text-zinc-200 mb-4 leading-relaxed text-sm">
                    Please present this QR code upon arrival for event check-in.
                    We’re excited to have you join us and look forward to your participation.
                </p>

                <p class="text-center text-zinc-500 dark:text-zinc-300 text-xs mt-2">
                    Tap “Arrow” to see your attendance details.
                </p>
            </div>

            <!-- ATTENDANCE SUMMARY SECTION -->
            <div class="absolute z-10 right-1/2 translate-x-1/2 flex flex-col items-center justify-center bg-white dark:bg-zinc-900 p-6 lg:px-14 rounded-xl shadow-xl w-full max-w-md aspect-auto transition-all duration-500 ease-in-out"
                :class="showSummary ? 'translate-x-130 opacity-100' : 'translate-x-0 opacity-0 pointer-events-none'">
                <h2 class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100 mb-4 text-center">
                    Attendance Summary
                </h2>

                <flux:separator variant="subtle" class="my-4" />

                <p class="text-center text-zinc-700 dark:text-zinc-200 mb-4 text-sm leading-relaxed">
                    Below is a summary of your confirmed attendance schedule.
                </p>

                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 mb-4">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Day</th>
                            <th scope="col" class="px-6 py-3">Date</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->guestData['attendances'] as $attendance)
                            @php
                                try {
                                    $date = \Carbon\Carbon::parse($attendance['attendance_date'])->startOfDay();
                                    $startDate = \Carbon\Carbon::parse($this->start_date)->startOfDay();
                                    $dayNumber = $startDate->diffInDays($date) + 1;
                                    $status = '';
                                    if ($attendance['status'] == 'checked_in') {
                                        $status = 'Checked In';
                                    } elseif ($attendance['status'] == 'not_checked_in') {
                                        $status = 'Not Checked In';
                                    }
                                } catch (\Exception $e) {
                                    continue; // Skip invalid dates
                                }
                            @endphp
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    Day {{ $dayNumber }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $date->format('D, d M Y') }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $status }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- <button @click="showSummary = false"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline transition mt-2">
                    Close Summary
                </button> --}}
            </div>

        </div>
    @endif
</div>


@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('verifyUser', (event) => {
                setTimeout(() => {
                    const userIcon = document.getElementById('userIcon');
                    const checkIcon = document.getElementById('checkIcon');

                    if (!userIcon || !checkIcon) return;

                    userIcon.classList.add('opacity-0', 'scale-0');
                    userIcon.classList.remove('opacity-100', 'scale-100');

                    checkIcon.classList.remove('opacity-0', 'scale-0');
                    checkIcon.classList.add('opacity-100', 'scale-100');
                }, 50);
            });
        });
    </script>
@endpush
