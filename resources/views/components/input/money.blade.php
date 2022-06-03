@props([
    'leadingIcon' => true,
])

<div class="flex rounded-md shadow-sm">
    @if ($leadingIcon)
    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 sm:text-sm">
        <span class="text-gray-400 sm:text-sm sm:leading-5">
            €
        </span>
    </span>
    @endif

    <input {{ $attributes }} type="number" step="0.01" class="form-input border-gray-300 block w-full text-gray-700 transition duration-150 ease-in-out sm:text-sm sm:leading-5 rounded-r-md @if(!$leadingIcon) rounded-l-md @endif" style="" placeholder="0,00" aria-describedby="price-currency">
</div>
