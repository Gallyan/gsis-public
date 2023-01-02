@props([
    'label' => '',
    'for' => '',
    'error' => false,
    'helpText' => false,
    'inline' => false,
    'paddingless' => false,
    'borderless' => false,
    'required' => false,
    'innerclass' => '',
])

@if($inline)
    <div {{ $attributes->only(['class']) }}>
        @if($label)
        <label for="{{ $for }}" class="block text-sm font-medium leading-5 text-gray-700 ml-1 mt-2 {{ $required ? 'required' : '' }}">{!! __($label) !!}</label>
        @endif

        <div class="mt-1 sm:mt-0 relative rounded-md {{ $innerclass }}">
            {{ $slot }}

            @if ( $error && is_array( $error ) )
                @foreach( $error as $messages )
                    @foreach( $messages as $msg )
                        <div class="mt-1 text-red-500 text-sm">{{ __($msg) }}</div>
                    @endforeach
                @endforeach
            @elseif ($error)
                <div class="mt-1 text-red-500 text-sm">{{ __($error) }}</div>
            @endif

            @if ($helpText)
                <p class="mt-2 text-sm text-gray-500">{{ __($helpText) }}</p>
            @endif
        </div>
    </div>
@else
    @php
        $classes = "sm:grid sm:grid-cols-5 sm:gap-4 sm:items-start sm:border-gray-200";
        if (!$borderless) $classes .= " sm:border-t";
        if (!$paddingless) $classes .= " sm:py-5";
    @endphp
    <div {{ $attributes->only(['class'])->merge(['class' => $classes]) }}>
        @if($label)
        <label for="{{ $for }}" class="block text-sm font-bold sm:font-medium leading-5 text-gray-700 sm:mt-2 pt-2 sm:pt-0 {{ $required ? 'required' : '' }}">{!! __($label) !!}</label>
        @endif

        <div class="mt-1 sm:mt-0 sm:col-span-4 {{ $innerclass }}">
            {{ $slot }}
        </div>

        @if ( $error && is_array( $error ) )
            @foreach( $error as $messages )
                @foreach( $messages as $msg )
                    <div class="mt-1 col-start-2 col-span-4 text-red-500 text-sm">{{ __($msg) }}</div>
                @endforeach
            @endforeach
        @elseif ($error)
            <div class="mt-1 col-start-2 col-span-4 text-red-500 text-sm">{!! __($error) !!}</div>
        @endif

        @if ($helpText)
            <p class="mt-1 col-start-2 col-span-4 text-sm text-gray-500">{!! __($helpText) !!}</p>
        @endif
    </div>
@endif
