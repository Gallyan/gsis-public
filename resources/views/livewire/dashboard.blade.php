<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Dashboard') }}</h1>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p>{{ __('Hi :name, you\'re logged in!',['name'=>$user->full_name]) }}</p>

                    @if ( session()->get('previous_login') && session()->get('previous_ip') )
                        <p>{{ __('Your last login was at :at from :from.', ['at'=>session()->get('previous_login'), 'from'=>session()->get('previous_ip')]) }}</p>
                        <p>{{ __('If it wasn\'t you, please contact an administrator.') }}</p>
                    @endif

                </div>
            </div>
        </div>
    </div>


</div>