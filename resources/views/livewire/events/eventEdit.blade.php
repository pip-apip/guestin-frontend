<?php

use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

new class extends Component {

    public function save()
    {
        foreach ($this->data as $key => $value) {
            if (empty($value)) {
                throw ValidationException::withMessages([
                    "data.$key" => 'The ' . str_replace('_', ' ', $key) . ' field is required.',
                ]);
            }
        }

        try {
            $response = Http::withToken(session('token'))->post(env('API_BASE_URL').'/events', $this->data)->json();

            if ($response['error'] === null) {
                return Redirect::route('events.index')->success('Event saved successfully!');
            } else {
                Toaster::error('Failed to save event: ' . ($response['error']));
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            Toaster::error('Failed to save event: ' . $e->getMessage());
        }
    }
}; ?>

<div>
    {{-- Header --}}
    <div class="relative mb-6 w-full">
        <div class="flex justify-between items-center">
            <div>
                <flux:heading size="xl" level="1" class="font-semibold text-gray-900 dark:text-white">
                    Form Add Event
                </flux:heading>
                <flux:subheading size="lg" class="text-gray-500 dark:text-gray-400">
                    Create a new event to manage your events list
                </flux:subheading>
            </div>
        </div>
    </div>
    <flux:separator variant="subtle" />

    <div class="space-y-6 mt-6">
        <flux:input label="Name Event" placeholder="Type the name of the event" wire:model.defer="data.name" />

        <flux:textarea label="Description Event" placeholder="Type the description of the event"
            wire:model="data.description" />

        <flux:input label="Location Event" placeholder="Type the location of the event"
            wire:model="data.location" />

        <div class="grid grid-cols-2 gap-2">
            <flux:input type="date" label="Start Date Event" wire:model="data.start_date" />
            <flux:input type="date" label="End Date Event" wire:model="data.end_date" />
        </div>

        <div class="grid grid-cols-2 gap-2">
            <flux:input type="time" label="Start Time Event" wire:model="data.start_time" />
            <flux:input type="time" label="End Time Event" wire:model="data.end_time" />
        </div>

        <flux:select label="Status" placeholder="Select event status" wire:model="data.status">
            <flux:select.option value="upcoming">Upcoming</flux:select.option>
        </flux:select>
        <div class="flex">
            <flux:spacer />
            <flux:button type="submit" variant="primary" wire:click="save">Save</flux:button>
        </div>
    </div>
</div>
