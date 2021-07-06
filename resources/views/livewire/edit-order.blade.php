<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Purchase Order') }}</h1>

    <form wire:submit.prevent="save" wire:reset.prevent="init">
        <div class="mt-6 sm:mt-5">
            @can ('manage-users')
            <x-input.group label="User" for="user">
                {{ $this->order->user->full_name ?? '' }}
            </x-input.group>
            @endcan

            <x-input.group label="Subject" for="subject" :error="$errors->first('order.subject')" required>
                <x-input.text wire:model.debounce.500ms="order.subject" id="subject" leading-add-on="" />
            </x-input.group>

            <x-input.group label="Institution" for="institution_id" :error="$errors->first('order.institution_id')" required>
                <x-input.select wire:model="order.institution_id" id="institution_id">
                    <x-slot name="placeholder">
                        Select Institution...
                    </x-slot>

                    @foreach (\App\Models\Institution::all()->sortBy('name') as $ins)
                    <option value="{{ $ins->id }}">{{ $ins->name }} / {{ $ins->contract }}</option>
                    @endforeach
                </x-input.select>
            </x-input.group>
    
            <x-input.group label="Supplier" for="supplier" :error="$errors->first('order.supplier')">
                <x-input.text wire:model.debounce.500ms="order.supplier" id="supplier" leading-add-on="" />
            </x-input.group>

            <x-input.group label="Comments" for="comments" :error="$errors->first('order.comments')">
                <x-input.textarea wire:model.lazy="order.comments" id="comments" rows="10" />
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

        <div class="mt-2 border-t border-gray-200 pt-5">
            <div class="space-x-3 flex justify-end items-center">
                <span x-data="{ open: false }" x-init="
                        @this.on('notify-saved', () => {
                            if (open === false) setTimeout(() => { open = false }, 2500);
                            open = true;
                        })
                    " x-show.transition.out.duration.1000ms="open" style="display: none;" class="text-gray-500">Saved!</span>

                <span class="inline-flex rounded-md shadow-sm">
                    <button type="reset" class="py-2 px-4 border border-gray-300 rounded-md text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out">
                        Cancel
                    </button>
                </span>

                <span class="inline-flex rounded-md shadow-sm">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
                        Save
                    </button>
                </span>
            </div>
        </div>
    </form>
</div>
