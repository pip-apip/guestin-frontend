<section x-data x-init="const modal = document.querySelector('[name=\'form-event-modal\']');

if (modal) {
    modal.addEventListener('flux:closed', () => {
        Livewire.dispatch('resetAddModal');
    });

    window.addEventListener('openEditModal', () => {
        modal.dispatchEvent(new CustomEvent('flux:open'));
    });
}">
    <div class="relative mb-6 w-full">
        <div class="flex justify-between text-bottom items-center">
            <span>
                <flux:heading size="xl" level="1">Events</flux:heading>
                <flux:subheading size="lg" class="mb-6">Manage your Events List</flux:subheading>
            </span>
            <flux:modal.trigger name="form-event-modal">
                <flux:button variant="primary" color="green" size="sm" wire:click="$set('addModal', true)">Add Event</flux:button>
            </flux:modal.trigger>
        </div>
        <flux:separator variant="subtle" />
    </div>

    <div class="overflow-x-auto">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            No
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Time
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Location
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Open QR</span>
                            <span class="sr-only">Edit</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($datas as $data )
                    <tr as-a href="#" class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-200 font-medium">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 text-gray-900 dark:text-gray-100 font-semibold">
                            {{ $data['name'] }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($data['start_date'])->format('D m Y') }} - {{ \Carbon\Carbon::parse($data['end_date'])->format('D m Y') }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($data['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($data['end_time'])->format('H:i') }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ $data['location'] }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ ucfirst(str_replace('_', ' ', $data['status'])) }}
                        </td>
                        <td class="px-6 py-4 text-right flex justify-end gap-3">
                            <flux:modal.trigger name="form-event-modal">
                                <flux:button variant="ghost" size="sm" wire:click="edit('{{ $data['slug'] }}')">
                                    Edit
                                </flux:button>
                            </flux:modal.trigger>
                            <flux:button variant="ghost" size="sm" wire:navigate href="{{ route('events.show', $data['slug']) }}">
                                Show
                            </flux:button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <flux:modal name="form-event-modal" class="md:w-200">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $addModal ? 'Add Event' : 'Edit Event' }}</flux:heading>
                <flux:text class="mt-2">Add a new event to your list</flux:text>
            </div>
            <flux:input label="Name Event" placeholder="Type the name of the event" wire:model.defer="editData.name" />

            <flux:textarea label="Description Event" placeholder="Type the description of the event" wire:model.defer="editData.description" />

            <flux:input label="Location Event" placeholder="Type the location of the event" wire:model.defer="editData.location" />

            <div class="grid grid-cols-2 gap-2">
                <flux:input type="date" label="Start Date Event" wire:model.defer="editData.start_date" />
                <flux:input type="date" label="End Date Event" wire:model.defer="editData.end_date" />
            </div>

            <div class="grid grid-cols-2 gap-2">
                <flux:input type="time" label="Start Time Event" wire:model.defer="editData.start_time" />
                <flux:input type="time" label="End Time Event" wire:model.defer="editData.end_time" />
            </div>

            <flux:select label="Status" placeholder="Select event status" wire:model.defer="editData.status">
                <flux:select.option value="Upcoming">Upcoming</flux:select.option>
                <flux:select.option value="Design services">Design services</flux:select.option>
                <flux:select.option value="Web development">Web development</flux:select.option>
            </flux:select>
            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary" wire:click="save">Save</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
