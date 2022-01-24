<div
    wire:ignore
    x-data="{pond: null}"
    x-init="
        pond = FilePond.create($refs.input, { credits: false });
        pond.setOptions({
            allowMultiple: {{ isset($attributes['multiple']) ? 'true' : 'false' }},
            maxFileSize: '{{ $attributes['maxFileSize'] ?? 'null' }}',
            @isset( $attributes['acceptedFileTypes'] )
                acceptedFileTypes: {!! $attributes['acceptedFileTypes'] ?? '[]' !!},
            @else
                allowFileTypeValidation: false,
            @endif
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
            labelMaxFileSizeExceeded: '{{ __('filepondMaxFileSizeExceeded') }}',
            labelMaxFileSize: '{{ __('filepondMaxFileSize') }}',
            labelMaxTotalFileSizeExceeded: '{{ __('filepondMaxTotalFileSizeExceeded') }}',
            labelMaxTotalFileSize: '{{ __('filepondMaxTotalFileSize') }}',
            labelFileTypeNotAllowed: '{{ __('filepondFileTypeNotAllowed') }}',
            fileValidateTypeLabelExpectedTypes: '{{ __('filepondValidateTypeLabelExpectedTypes') }}',
            fileValidateTypeLabelExpectedTypesMap: { 'image/jpeg': '.jpg', 'image/gif': '.gif', 'image/jpg': '.jpg', 'image/png': '.png', 'image/svg+xml': '.svg', 'image/webp': '.webp', 'image/bmp': '.bmp', 'application/vnd.ms-excel': '.xls', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': '.xlsx', 'application/msword': '.doc', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '.docx', 'application/pdf': '.pdf', 'image/*': '.jpg, .png, .gif, .svg, .bmp', 'application/zip': '.zip' },
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
        <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
        <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
        <script>
            FilePond.registerPlugin(FilePondPluginFileValidateSize);
            FilePond.registerPlugin(FilePondPluginFileValidateType);
        </script>
    @endonce
@endpush