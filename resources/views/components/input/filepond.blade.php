<div
    wire:ignore
    x-data="{pond: null}"
    x-init="
        pond = FilePond.create($refs.input, { credits: false });
        pond.setOptions({
            allowMultiple: {{ isset($attributes['multiple']) ? 'true' : 'false' }},
            labelIdle: '{{ __('filepondIdle') }}',
            labelFileProcessingComplete: '{{ __('filepondFileProcessingComplete') }}',
            labelInvalidField: '{{ __('filepondInvalidField') }}',
            labelFileWaitingForSize: '{{ __('filepondFileWaitingForSize') }}',
            labelFileSizeNotAvailable: '{{ __('filepondFileSizeNotAvailable') }}',
            labelFileLoading: '{{ __('filepondFileLoading') }}',
            labelFileLoadError: '{{ __('filepondFileLoadError') }}',
            labelFileProcessing: '{{ __('filepondFileProcessing') }}',
            labelFileProcessingAborted: '{{ __('filepondFileProcessingAborted') }}',
            labelFileProcessingError: '{{ __('filepondFileProcessingError') }}',
            labelFileProcessingRevertError: '{{ __('filepondFileProcessingRevertError') }}',
            labelFileRemoveError: '{{ __('filepondFileRemoveError') }}',
            labelTapToCancel: '{{ __('filepondTapToCancel') }}',
            labelTapToRetry: '{{ __('filepondTapToRetry') }}',
            labelTapToUndo: '{{ __('filepondTapToUndo') }}',
            labelButtonRemoveItem: '{{ __('filepondButtonRemoveItem') }}',
            labelButtonAbortItemLoad: '{{ __('filepondButtonAbortItemLoad') }}',
            labelButtonRetryItemLoad: '{{ __('filepondButtonRetryItemLoad') }}',
            labelButtonAbortItemProcessing: '{{ __('filepondButtonAbortItemProcessing') }}',
            labelButtonUndoItemProcessing: '{{ __('filepondButtonUndoItemProcessing') }}',
            labelButtonRetryItemProcessing: '{{ __('filepondButtonRetryItemProcessing') }}',
            labelButtonProcessItem: '{{ __('filepondButtonProcessItem') }}',
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
    @once
        <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    @endonce
@endpush

@push('scripts')
    @once
        <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    @endonce
@endpush