@props([
    'label' => '',
    'for' => '',
    'error' => false,
    'helpText' => false,
    'inline' => false,
    'paddingless' => false,
    'borderless' => false,
    'required' => false,
    'class' => '',
    'innerclass' => '',
])

@if($inline)
    <div class="{{ $class }}">
        @if($label)
        <label for="{{ $for }}" class="block text-sm font-medium leading-5 text-gray-700 ml-1 {{ $required ? 'required' : '' }}">{!! __($label) !!}</label>
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
    <div class="sm:grid sm:grid-cols-5 sm:gap-4 sm:items-start {{ $borderless ? '' : ' sm:border-t ' }} sm:border-gray-200 {{ $paddingless ? '' : ' sm:py-5 ' }} {{ $class }}">
        @if($label)
        <label for="{{ $for }}" class="block text-sm font-bold sm:font-medium leading-5 text-gray-700 sm:mt-px pt-4 {{ $required ? 'required' : '' }}">{!! __($label) !!}</label>
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
