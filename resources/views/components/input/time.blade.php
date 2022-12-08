

<div class="flex rounded-md shadow-sm">
    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 sm:text-sm">
        <span class="text-gray-400 sm:text-sm sm:leading-5">
            <x-icon.clock />
        </span>
    </span>

    <input {{ $attributes }} type="time" class="form-input border-gray-300 block w-40 text-center text-gray-700 transition duration-150 ease-in-out sm:text-sm sm:leading-5 rounded-r-md" aria-describedby="time" pattern="[0-2][0-9]:[0-5][0-9]">
</div>