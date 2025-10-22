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
                <flux:button variant="primary" color="green" wire:click="$set('addModal', true)">Add Event</flux:button>
            </flux:modal.trigger>
        </div>
        <flux:separator variant="subtle" />
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Event Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Start Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">End Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($datas as $data)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $data['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($data['start_date'])->locale('id')->translatedFormat('d F Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($data['end_date'])->locale('id')->translatedFormat('d F Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $data['location'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center gap-2 flex justify-center">
                            <flux:modal.trigger name="form-event-modal">
                                <flux:button variant="primary" color="yellow"
                                    wire:click="edit('{{ $data['slug'] }}')">
                                    Edit
                                </flux:button>
                            </flux:modal.trigger>

                            <flux:button variant="primary" color="red">Delete</flux:button>
                            <flux:button variant="primary" color="indigo" icon="ellipsis-vertical"></flux:button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <flux:modal name="form-event-modal" class="md:w-200">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $addModal ? 'Add Event' : 'Edit Event' }}</flux:heading>
                <flux:text class="mt-2">Add a new event to your list</flux:text>
            </div>
            <flux:input label="Name Event" placeholder="Type the name of the event" wire:model.defer="editData.name" />

            <flux:textarea label="Description Event" placeholder="Type the description of the event"
                wire:model.defer="editData.description" />

            <flux:input label="Location Event" placeholder="Type the location of the event"
                wire:model.defer="editData.location" />

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
