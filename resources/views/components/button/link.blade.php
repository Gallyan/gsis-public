<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' => 'text-cool-gray-700 text-sm leading-5 font-medium focus:outline-none focus:shadow-outline-blue focus:text-cool-gray-800 p-1 rounded-sm transition duration-150 ease-in-out' . ($attributes->get('disabled') ? ' opacity-75 cursor-not-allowed' : ''),
    ]) }}
>
    {{ $slot }}
</button>
