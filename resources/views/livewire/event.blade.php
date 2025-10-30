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
            <flux:button variant="primary" size="sm" wire:navigate href="{{ route('events.create') }}">
                Add Event
            </flux:button>
        </div>
        <flux:separator variant="subtle" />
    </div>

    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-800 p-6">
        <div class="justify-between mb-6 grid grid-flow-col grid-cols-3 gap-4 px-5 py">
            <div class="flex gap-4 col-span-2">
                <div class="flex justify-between gap-4">
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Search</flux:label>
                    <flux:input type="search" placeholder="Search events..." wire:model.live="searchQuery" />
                </div>
                <div class="flex justify-between gap-4">
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Status</flux:label>
                    <flux:select wire:model.live="searchStatus" placeholder="Select Status ...">
                        <flux:select.option value="upcoming">Upcoming</flux:select.option>
                        <flux:select.option value="ongoing">Ongoing</flux:select.option>
                        <flux:select.option value="complete">Complete</flux:select.option>
                        <flux:select.option value="canceled">Canceled</flux:select.option>
                    </flux:select>
                </div>
            </div>
            <div class="flex justify-between gap-4">
                <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Sort</flux:label>
                <flux:select wire:model.live="filterSort" placeholder="Sort By">
                    <flux:select.option value="name">Name</flux:select.option>
                    <flux:select.option value="start_date">Start Date</flux:select.option>
                    <flux:select.option value="end_date">End Date</flux:select.option>
                    <flux:select.option value="start_time">Time Start</flux:select.option>
                    <flux:select.option value="end_time">Time End</flux:select.option>
                    <flux:select.option value="location">Location</flux:select.option>
                </flux:select>
                <flux:select wire:model.live="filterOrder" placeholder="Order">
                    <flux:select.option value="asc">ASC</flux:select.option>
                    <flux:select.option value="desc">DESC</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800">
            <table class="min-w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-zinc-800 text-xs uppercase text-gray-500 dark:text-gray-400">
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
                    @if (count($datas) === 0)
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No events found.
                            </td>
                        </tr>
                    @else
                        @foreach ($datas as $data)
                            <tr as-a href="#"
                                wire:key="{{ $data['id'] }}"
                                class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-200 font-medium">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 text-gray-900 dark:text-gray-100 font-semibold">
                                    {{ $data['name'] }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($data['start_date'])->format('D, d M Y') }} -
                                    {{ \Carbon\Carbon::parse($data['end_date'])->format('D, d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($data['start_time'])->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($data['end_time'])->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ $data['location'] }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ ucfirst(str_replace('_', ' ', $data['status'])) }}
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-3">
                                    <flux:modal.trigger name="form-event-modal">
                                        <flux:button variant="ghost" size="sm" wire:navigate
                                            href="{{ route('events.edit', $data['slug']) }}">
                                            Edit
                                        </flux:button>
                                    </flux:modal.trigger>
                                    <flux:button variant="ghost" size="sm" wire:navigate
                                        href="{{ route('events.show', $data['slug']) }}">
                                        Show
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</section>
