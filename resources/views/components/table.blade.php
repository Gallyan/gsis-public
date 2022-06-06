<div class="align-middle min-w-full overflow-x-auto shadow overflow-hidden sm:rounded-lg">
    <table class="min-w-full divide-y divide-cool-gray-200">
        @isset ($head)
        <thead>
            <tr>
                {{ $head }}
            </tr>
        </thead>
        @endisset

        <tbody class="bg-white divide-y divide-cool-gray-200">
            {{ $body }}
        </tbody>
    </table>
</div>
