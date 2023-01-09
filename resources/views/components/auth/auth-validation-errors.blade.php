@props(['errors'])

@if($errors instanceof Illuminate\Support\ViewErrorBag)

@if ($errors->any())
    <div {{ $attributes }}>
        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@elseif (is_array($errors))

@if (!empty($errors))
    <div {{ $attributes }}>
        <ul class="mt-1 list-disc list-inside text-xs text-red-600">
            @foreach ($errors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@endif