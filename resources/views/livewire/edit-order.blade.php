<div class="relative">
    <form wire:submit.prevent="save" wire:reset.prevent="init">
        @csrf

        <x-stickytopbar title="{{ __('Purchase Order') }} {{ $order->id }}" />

        <div class="mt-6 sm:mt-5">
            @can ('manage-users')
            <x-input.group label="User" for="user" class="sm:items-center text-cool-gray-600 sm:pb-5" paddingless borderless>
                {{ $this->order->user->full_name ?? '' }}
            </x-input.group>

            <x-input.group label="Subject" for="subject" :error="$errors->first('order.subject')" required>
                <x-input.text wire:model.debounce.500ms="order.subject" id="subject" leading-add-on="" />
            </x-input.group>
            @else
            <x-input.group label="Subject" for="subject" :error="$errors->first('order.subject')" borderless required>
                <x-input.text wire:model.debounce.500ms="order.subject" id="subject" leading-add-on="" />
            </x-input.group>
            @endcan

            <x-input.group label="Institution" for="institution_id" :error="$errors->first('order.institution_id')" required>
                <x-input.select wire:model="order.institution_id" id="institution_id" placeholder="Select Institution...">
                    @foreach (\App\Models\Institution::all()->sortBy('name') as $ins)
                    <option value="{{ $ins->id }}">{{ $ins->name }} / {{ $ins->contract }}</option>
                    @endforeach
                </x-input.select>
            </x-input.group>

            <x-input.group label="Supplier" for="supplier" :error="$errors->first('order.supplier')">
                <x-input.text wire:model.debounce.500ms="order.supplier" id="supplier" leading-add-on="" />
            </x-input.group>

            <x-input.group label="Books" for="books" wire:model="order.books" :error="$errors->first('order.books')">
                    <x-table>
                        <x-slot name="head">
                            <x-table.heading class="w-full" small>{{ __('Title') }}</x-table.heading>
                            <x-table.heading class="min-w-200" small>{{ __('Author') }}</x-table.heading>
                            <x-table.heading class="min-w-200" small>{{ __('ISBN') }}</x-table.heading>
                            <x-table.heading small>{{ __('Actions') }}</x-table.heading>
                        </x-slot>

                        <x-slot name="body">
                            @forelse ($order->books as $book)
                            <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">
                                <x-table.cell>
                                    <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                        <p class="text-cool-gray-600 truncate">
                                            {{ $book['title'] }}
                                        </p>
                                    </span>
                                </x-table.cell>

                                <x-table.cell>
                                    <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                        <p class="text-cool-gray-600 truncate">
                                            {{ $book['author'] }}
                                        </p>
                                    </span>
                                </x-table.cell>

                                <x-table.cell class="text-center">
                                    <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                        <p class="text-cool-gray-600 truncate">
                                            {{ $book['isbn'] }}
                                        </p>
                                    </span>
                                </x-table.cell>

                                <x-table.cell class="text-center">
                                    <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                        <x-button.link wire:click="edit_book({{ $loop->iteration }})" class="text-cool-gray-600 truncate" title="{{ __('Edit') }}">
                                            <x-icon.pencil class="h-4 w-4 text-cool-gray-400" />
                                        </x-button.link>
                                        <x-button.link wire:click="del_book({{ $loop->iteration }})" class="text-cool-gray-600 truncate"  title="{{ __('Delete') }}">
                                            <x-icon.trash class="h-4 w-4 text-cool-gray-400" />
                                        </x-button.link>
                                    </span>
                                </x-table.cell>
                            </x-table.row>
                            @empty
                            <x-table.row>
                                <x-table.cell colspan="6">
                                    <div class="flex justify-center items-center space-x-2">
                                        <x-icon.inbox class="h-6 w-6 text-cool-gray-400" />
                                        <span class="font-medium text-cool-gray-400 text-lg">{{ __('No books...') }}</span>
                                    </div>
                                </x-table.cell>
                            </x-table.row>
                            @endforelse
                        </x-slot>
                    </x-table>

                    <x-button.secondary wire:click="$set('showModal', true)" class="mt-4"><x-icon.plus/> {{ __('Add book') }}</x-button.primary>

                </x-input.group>

            <x-input.group label="Comments" for="comments" :error="$errors->first('order.comments')">
                <x-input.textarea wire:model.lazy="order.comments" id="comments" rows="5" />
            </x-input.group>

            <x-input.group label="Status" for="status" :error="$errors->first('order.status')" required>
                <x-input.select wire:model="order.status" id="status">
                    <x-slot name="placeholder">
                        Select Status...
                    </x-slot>
                    @foreach (\App\Models\Order::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ __($label) }}</option>
                    @endforeach
                </x-input.select>
            </x-input.group>
        </div>
    </form>

    <!-- Add book Modal -->
    <form wire:submit.prevent="add_book">
        @csrf

        <x-modal.dialog wire:model.defer="showModal">
            <x-slot name="title">@if(isset($this->book_id)) @lang('Edit book') @else @lang('Add book') @endif</x-slot>

            <x-slot name="content">
                <x-input.group for="title" label="Title" :error="$errors->first('title')" required>
                    <x-input.text wire:model.debounce.500ms="title" id="title" placeholder="Title"/>
                </x-input.group>

                <x-input.group for="author" label="Author" :error="$errors->first('author')" required>
                    <x-input.text wire:model.debounce.500ms="author" id="author" placeholder="Author"/>
                </x-input.group>

                <x-input.group for="isbn" label="Isbn" :error="$errors->first('isbn')" required>
                    <x-input.text wire:model.debounce.500ms="isbn" id="isbn" placeholder="Isbn"/>
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="close_modal">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary type="submit">@if(isset($this->book_id)) @lang('Update') @else @lang('Add') @endif</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>
</div>
