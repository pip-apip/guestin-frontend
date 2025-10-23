<section class="w-full">
    <div x-data="{ showGuestModal: false }" x-on:open-modal.window="showGuestModal = true" x-on:close-modal.window="showGuestModal = false" x-on:toast.window="Flux.toast[$event.detail.type]($event.detail.message)" x-on:close-modal.window="Flux.modal.close($event.detail.id)">


        <div class="relative mb-6 w-full">
            <div class="flex justify-between text-bottom items-center">
                <span>
                    <flux:heading size="xl" level="1">Guest</flux:heading>
                    <flux:subheading size="lg" class="mb-6">Manage your Guest List</flux:subheading>
                </span>
                <flux:modal.trigger name="form-guest-modal">
                    <flux:button variant="primary" size="sm">Add Guest</flux:button>
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
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3">
                                phone
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Event
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
                                {{ $data['email'] }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                {{ $data['phone'] }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                {{ $data['event']['name'] }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                {{ ucfirst(str_replace('_', ' ', $data['status'])) }}
                            </td>
                            <td class="px-6 py-4 text-right flex justify-end gap-3">
                                <flux:modal.trigger name="qr-modal-{{ $data['id'] }}">
                                    @if($data['qr_generated'] !== null)
                                    <flux:button class="text-sm" :disabled="$data['qr_generated'] == null" variant="primary" size="sm">
                                        Lihat QR
                                    </flux:button>
                                    @else
                                    <flux:tooltip content="QR Code not generated yet">
                                        <div>
                                            <flux:button disabled size="sm">Lihat QR</flux:button>
                                        </div>
                                    </flux:tooltip>
                                    @endif

                                </flux:modal.trigger>
                                <flux:modal.trigger name="form-guest-modal" >
                                    <flux:button variant="ghost" size="sm">
                                        Edit
                                    </flux:button>
                                </flux:modal.trigger>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modals -->
        @foreach ($datas as $modal)
        @if($modal['qr_generated'])
        <flux:modal name="qr-modal-{{ $modal['id'] }}" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">QR Code for {{ $modal['name'] }} </flux:heading>
                    <flux:description class="mt-2">Scan this QR code at the event check-in.</flux:description>
                </div>
                <div class="flex items-center justify-center">
                    {!! $modal['qr_generated'] !!}
                </div>
                <div class="flex">
                    <flux:spacer />
                    <flux:button x-on:click="$flux.modals().close()">Close</flux:button>
                </div>
            </div>
        </flux:modal>
        @endif
        @endforeach

        <flux:modal name="form-guest-modal" class="md:w-200">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $addModal ? 'Add Guest' : 'Edit Guest' }}</flux:heading>
                    <flux:text class="mt-2">Add a new guest to your list</flux:text>
                </div>
                <flux:input label="Guest Name" placeholder="Type the name of the guest" wire:model.defer="guestData.name" />
                <flux:select label="Event" placeholder="Select event name" wire:model.defer="guestData.event_id">
                    @foreach ($eventDatas as $data)
                    <flux:select.option value="{{ $data['id'] }}">{{ $data['name'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input type="email" label="Email Guest" placeholder="Type the email of the guest" wire:model.defer="guestData.email" />
                <flux:input label="Phone Guest" placeholder="Type the phone of the guest" wire:model.defer="guestData.phone" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="primary" wire:click="save">Save</flux:button>
                </div>
            </div>
        </flux:modal>
    </div>

</section>
