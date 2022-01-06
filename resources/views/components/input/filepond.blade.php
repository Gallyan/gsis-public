<div
    wire:ignore
    x-data="{pond: null}"
    x-init="
        pond = FilePond.create($refs.input, { credits: false });
        pond.setOptions({
            allowMultiple: {{ isset($attributes['multiple']) ? 'true' : 'false' }},
            server: {
                process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                    @this.upload('{{ $attributes['wire:model'] }}', file, load, error, progress)
                },
                revert: (filename, load) => {
                    @this.removeUpload('{{ $attributes['wire:model'] }}', filename, load)
                },
            },
        });"
    {{ $attributes->only('class') }}
>

    <input type="file" name="{{ $inputname }}" x-ref="input" />

</div>