<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

new #[Layout('components.layouts.app-landing')] class extends Component {
    public $guestCode = '';
    public $guestData = [];
    public $url = '';
    public $cardNumber;

    public $dateAttendance = '';

    public function mount()
    {
        $this->guestCode = $this->getQueryStringParams();
        $this->guestData = $this->getGuestData();
        // dd($this->guestData);
        if ($this->guestData['guest']['confirm_attendance'] === null) {
            $cardNumber = 2;
        } else {
            $cardNumber = 1;
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
        $response = Http::get(env('API_BASE_URL') . '/guests/confirm-guest/' . $this->guestCode);
        if ($response->successful()) {
            return $response->json('data');
        } else {
            return null;
        }
    }

    public function confirmAttendance()
    {
        // $baseUrl = env('API_BASE_URL');
        // $response = Http::post($this->url, [
        //     'available_date' => $this->dateAttendance,
        //     'status' => 'confirmed',
        // ]);

        // if ($response->successful()) {
        //     session()->flash('success', $response->json('message'));
        $this->dispatch('verifyUser');
        // } else {
        //     session()->flash('error', 'Failed to confirm attendance. Please try again.');
        //     \Log::error('Error confirming attendance: ' . $response->json());
        // }
    }
}; ?>

<div class="w-full flex items-center justify-center">
    <div
        class="flex flex-col items-center justify-center bg-white dark:bg-zinc-900 p-6 lg:px-14 rounded-xl shadow-xl w-full max-w-md aspect-auto">

        <!-- Heading -->
        <h1 class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100 mb-4 text-center">
            Confirm Your Attendance
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

            <!-- Check Icon -->
            <svg id="checkIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="0.5"
                stroke="currentColor"
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
                <strong>{{ \Carbon\Carbon::parse($guestData['event']['start_date'])->setTimezone('Asia/Jakarta')->format('D d M Y') }}</strong>.
            @else
                <strong>{{ \Carbon\Carbon::parse($guestData['event']['start_date'])->format('D, d M Y') }}</strong>
                to
                <strong>{{ \Carbon\Carbon::parse($guestData['event']['end_date'])->format('D, d M Y') }}</strong>
            @endif
            Please select your attendance date to confirm your participation in this event.
        </p>

        <!-- Date Input -->
        <div class="w-full max-w-xs mb-12 mt-6">
            <flux:input type="date" label="Attendance Date" wire:model.defer="dateAttendance" />
        </div>

        <!-- Confirm Button -->
        <button wire:click="confirmAttendance" type="submit"
            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow transition">
            Confirm Attendance
        </button>
    </div>
</div>


@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            window.addEventListener('verifyUser', (event) => {
                const userIcon = document.getElementById('userIcon');
                const checkIcon = document.getElementById('checkIcon');

                userIcon.classList.add('opacity-0', 'scale-0');
                userIcon.classList.remove('opacity-100', 'scale-100');

                checkIcon.classList.remove('opacity-0', 'scale-0');
                checkIcon.classList.add('opacity-100', 'scale-100');
            });
        });
    </script>
@endpush
