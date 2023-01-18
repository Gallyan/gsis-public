<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Dashboard') }}</h1>

    <div class="py-8 grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">

        <div class="col-span-2 bg-white overflow-hidden shadow-md sm:rounded-lg">
            <div class="p-6 bg-white text-md">
                <p>{{ __('Hi :name, you\'re logged in!',['name'=>$user->name]) }}</p>
            </div>

            @if ( session()->get('previous_login') && session()->get('previous_ip') )
            <div class="p-6 bg-white text-sm text-gray-700 border-t border-gray-200">
                @php
                    \Illuminate\Support\Carbon::setlocale(config('app.locale'));
                @endphp
                    <p>{{ __('Your last login was on :at from IP :from.', ['at'=>\Illuminate\Support\Carbon::parse(session()->get('previous_login'))->translatedFormat(__('lastlog-dt')), 'from'=>session()->get('previous_ip')]) }} {{ __('If it wasn\'t you, please contact an administrator.') }}</p>
            </div>
            @endif

            @if ( $user->verified === false )
            <div class="p-6 bg-white text-md border-t border-gray-200">
                <p><x-icon.warning class="mr-2 flex-shrink-0 h-8 w-8 text-red-400" />{{ __('Your email address is not yet validated. For the moment, you only have access to your profile and your dashboard. You can request a new verification email by clicking on the button below:') }}</p>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-auth.button class="mt-4">
                        {{ __('Resend Verification Email') }}
                    </x-auth.button>
                </form>
                @if (session('status') == 'verification-link-sent')
                <div class="mt-4 font-medium text-sm text-green-600">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
                @endif
            </div>
            @endif

            @if ( $user->missingInfo !== [] )
            <div class="p-6 bg-white text-md border-t border-gray-200">
                <p><x-icon.warning class="mr-2 flex-shrink-0 h-8 w-8 text-red-400" />
                {{ trans_choice('incomplete-profile', count($user->missingInfo), ['missing'=>implode(', ',$user->missingInfo)]) }} {!! __('please-complete', ['profile' => route( 'edit-user', auth()->id() ) ] ) !!}</p>
            </div>
            @endif
        </div>

        <div class="col-span-2 bg-white overflow-hidden shadow-md sm:rounded-lg">
            <div class="p-6 bg-white text-md">
                {{ __('You have stated') }}
                {{ count($user->missions) }} {{ __('missions') }},
                {{ count($user->expenses) }} {{ strtolower(__('Mission expenses')) }},
                {{ count($user->purchases) }} {{ __('purchases') }},
                {{ count($user->orders) }} {{ __('orders') }}.
            </div>
        </div>

        @if( auth()->user()->can('manage-admin') )
        <div class="col-span-1 bg-white overflow-hidden shadow-md sm:rounded-lg flex flex-col">
            <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-pink-600 to-pink-400 text-white shadow-pink-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                <x-icon.user fill="currentColor" class="w-8 h-8"/>
            </div>
            <div class="p-4 text-right">
                <p class="block text-sm text-gray-500">
                    @lang('Connected Users')
                </p>
                <h4 class="block tracking-normal text-3xl font-semibold leading-snug text-gray-900">
                    {{ count(
                    \App\Models\User::where('last_seen_at', '>', Illuminate\Support\Carbon::now()->subMinutes(30)->toDateTimeString())
                                    ->get()
                    ) }}
                </h4>
            </div>
            <div class="border-t border-blue-gray-50 p-4">
                <p class="block text-sm font-normal text-gray-500">
                {{ count(
                    \App\Models\User::whereDate('last_seen_at', Illuminate\Support\Carbon::today())
                                    ->get()
                    ) }} @lang('have connected today')
                </p>
            </div>
        </div>
        @endif

    </div>

</div>