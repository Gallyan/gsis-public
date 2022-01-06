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
        });
        this.addEventListener('pondReset', e => {
            pond.removeFiles();
        });"
    {{ $attributes->only('class') }}
>

    <input type="file" name="{{ $inputname }}" x-ref="input" />

</div>

@push('stylesheets')
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
@endpush