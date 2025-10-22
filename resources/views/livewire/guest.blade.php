<section class="w-full">
    <div x-data="{ showGuestModal: false }" x-on:open-modal.window="showGuestModal = true"
        x-on:close-modal.window="showGuestModal = false"
        x-on:toast.window="Flux.toast[$event.detail.type]($event.detail.message)"
        x-on:close-modal.window="Flux.modal.close($event.detail.id)">


        <div class="relative mb-6 w-full">
            <div class="flex justify-between text-bottom items-center">
                <span>
                    <flux:heading size="xl" level="1">Guest</flux:heading>
                    <flux:subheading size="lg" class="mb-6">Manage your Guest List</flux:subheading>
                </span>
                <flux:modal.trigger name="form-guest-modal">
                    <flux:button variant="primary" color="green">Add Guest</flux:button>
                </flux:modal.trigger>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y ">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Guest Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Event Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($datas as $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $data['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $data['event']['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $data['phone'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $data['email'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center gap-2 flex justify-center">
                                <flux:button variant="primary" color="yellow">Edit</flux:button>
                                <flux:button variant="primary" color="red">Delete</flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <flux:modal name="form-guest-modal" class="md:w-200">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $addModal ? 'Add Guest' : 'Edit Guest' }}</flux:heading>
                    <flux:text class="mt-2">Add a new guest to your list</flux:text>
                </div>
                <flux:input label="Guest Name" placeholder="Type the name of the guest"
                    wire:model.defer="guestData.name" />
                <flux:select label="Event" placeholder="Select event name" wire:model.defer="guestData.event_id">
                    @foreach ($eventDatas as $data)
                        <flux:select.option value="{{ $data['id'] }}">{{ $data['name'] }}</flux:select.option>
                    @endforeach
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

    </div>

    <div wire:key="toast-{{ now() }}">
        @if (session('success'))
            <x-toast type="success" :message="session('success')" />
        @endif

        @if (session('error'))
            <x-toast type="error" :message="session('error')" />
        @endif
    </div>
</section>
