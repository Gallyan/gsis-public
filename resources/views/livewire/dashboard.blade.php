<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Dashboard') }}</h1>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p>{{ __('Hi :name, you\'re logged in!',['name'=>$user->name]) }}</p>
                </div>
                @if ( session()->get('previous_login') && session()->get('previous_ip') )
                <div class="p-6 bg-white text-sm">
                    @php
                        \Illuminate\Support\Carbon::setlocale(config('app.locale'));
                    @endphp
                        <p>{{ __('Your last login was on :at from IP :from.', ['at'=>\Illuminate\Support\Carbon::parse(session()->get('previous_login'))->translatedFormat(__('lastlog-dt')), 'from'=>session()->get('previous_ip')]) }}</p>
                        <p>{{ __('If it wasn\'t you, please contact an administrator.') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>


</div>