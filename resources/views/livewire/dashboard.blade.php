<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Dashboard') }}</h1>

    <div class="py-8 grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">

        <div class="mx-auto col-span-full w-full">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white text-md">
                    <p>{{ __('Hi :name, you\'re logged in!',['name'=>$user->name]) }}</p>
                </div>

                @if ( session()->get('previous_login') && session()->get('previous_ip') )
                <div class="p-6 bg-white text-md border-t border-gray-200">
                    @php
                        \Illuminate\Support\Carbon::setlocale(config('app.locale'));
                    @endphp
                        <p>{{ __('Your last login was on :at from IP :from.', ['at'=>\Illuminate\Support\Carbon::parse(session()->get('previous_login'))->translatedFormat(__('lastlog-dt')), 'from'=>session()->get('previous_ip')]) }}</p>
                        <p>{{ __('If it wasn\'t you, please contact an administrator.') }}</p>
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
        </div>

        <div class="mx-auto col-span-full w-full">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white text-md">
                    {{ __('Vous avez déclaré') }}
                    {{ count($user->missions) }} {{ __('missions') }},
                    {{ count($user->purchases) }} {{ __('purchases') }},
                    {{ count($user->orders) }} {{ __('orders') }}.
                </div>
            </div>
        </div>

    </div>

</div>