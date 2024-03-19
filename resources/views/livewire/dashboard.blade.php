<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Dashboard') }}</h1>

    <div class="py-8 grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8">

        <div class="lg:col-span-2 bg-white overflow-hidden shadow-md sm:rounded-lg">
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

        <div class="col-span-1 bg-white overflow-hidden shadow-md sm:rounded-lg flex flex-col">
            <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-yellow-600 to-yellow-400 text-white shadow-yellow-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                <x-icon.stats fill="currentColor" class="w-8 h-8"/>
            </div>
            <div class="p-4 text-right">
                <p class="block text-sm text-gray-500">
                    @lang('Your activity')
                </p>
            </div>
            <div class="border-t border-blue-gray-50 p-4">
                <p class="block font-normal text-gray-600">{{ __('You have created:') }}</p>
                <p class="block font-normal text-gray-600"><a href="{{ route('missions',['f'=>['user'=>$user->name]]) }}"><strong>{{ count($user->missions) }}</strong>&nbsp;{{ __('missions') }}</a></p>
                <p class="block font-normal text-gray-600"><a href="{{ route('expenses',['f'=>['user'=>$user->name]]) }}"><strong>{{ count($user->expenses) }}</strong>&nbsp;{{ strtolower(__('Mission expenses')) }}</a></p>
                <p class="block font-normal text-gray-600"><a href="{{ route('purchases',['f'=>['user'=>$user->name]]) }}"><strong>{{ count($user->purchases) }}</strong>&nbsp;{{ __('purchases') }}</a></p>
                <p class="block font-normal text-gray-600"><a href="{{ route('orders',['f'=>['user'=>$user->name]]) }}"><strong>{{ count($user->orders) }}</strong>&nbsp;{{ __('orders') }}</a></p>
            </div>
        </div>

        <!-- Connected Users //-->
        @if( auth()->user()->can('manage-admin') )
        <div class="col-span-1 bg-white overflow-hidden shadow-md sm:rounded-lg flex flex-col">
            <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-pink-600 to-pink-400 text-white shadow-pink-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                <x-icon.user fill="currentColor" class="w-8 h-8"/>
            </div>
            <div class="p-4 text-right">
                <p class="block text-sm text-gray-500">
                    @lang('Connected users')
                </p>
            </div>
            <div class="border-t border-blue-gray-50 p-4">
                <p class="block text-base font-normal text-gray-600">
                    <strong>{{ \App\Models\User::sinceMinutes(1) }}</strong> @lang('since :nb minutes',['nb'=>1])
                </p>
                <p class="block text-base font-normal text-gray-600">
                    <strong>{{ \App\Models\User::sinceMinutes(5) }}</strong> @lang('since :nb minutes',['nb'=>5])
                </p>
                <p class="block text-base font-normal text-gray-600">
                    <strong>{{ \App\Models\User::sinceMinutes(30) }}</strong> @lang('since :nb minutes',['nb'=>30])
                </p>
                <p class="block text-base font-normal text-gray-600">
                    <strong>{{ \App\Models\User::sinceMinutes() }}</strong> @lang('have connected today')
                </p>
            </div>
        </div>
        @endif
        <!-- End Connected Users //-->

        <!-- Demande en attente //-->
        @if( auth()->user()->can('manage-users') )
        <div class="lg:col-span-2 bg-white overflow-hidden shadow-md sm:rounded-lg flex flex-col">
            <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-green-600 to-green-400 text-white shadow-green-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                <x-icon.rss fill="currentColor" class="w-8 h-8"/>
            </div>
            <div class="p-4 text-right">
                <p class="block text-sm text-gray-500">
                    @lang('Pending applications')
                </p>
            </div>
            <div class="border-t border-blue-gray-50 p-4">
                @php
                $missions = DB::table('missions')
                            ->select(DB::raw("'Mission' as type"),'id','user_id','subject','created_at')
                            ->whereStatus('on-hold');

                $expenses = DB::table('expenses')
                            ->select(DB::raw("'Expenses' as type"),'expenses.id','expenses.user_id','missions.subject','expenses.created_at')
                            ->leftJoin('missions', 'missions.id', '=', 'expenses.mission_id')
                            ->where('expenses.status','on-hold');

                $orders = DB::table('orders')
                            ->select(DB::raw("'Order' as type"),'id','user_id','subject','created_at')
                            ->whereStatus('on-hold');

                $purchases = DB::table('purchases')
                            ->select(DB::raw("'Purchases' as type"),'id','user_id','subject','created_at')
                            ->whereStatus('on-hold');

                $pending = $missions
                            ->union($expenses)
                            ->union($orders)
                            ->union($purchases)
                            ->orderBy('created_at','ASC')
                            ->get();
                @endphp

                @forelse ($pending as $todo)
                <p wire:loading.class.delay="opacity-50"
                   wire:key="row-{{ $loop->iteration }}"
                   wire:click="edit('{{$todo->type}}',{{$todo->id}})"
                   class="cursor-pointer hover:bg-gray-100 {{ $loop->even ? 'bg-cool-gray-50' : '' }} whitespace-normal px-4 py-2 text-sm leading-5 text-cool-gray-600 {{ $loop->first ? 'rounded-t-md' : '' }} {{ $loop->last ? 'rounded-b-md' : '' }}">
                    <span class="font-semibold">{{ __($todo->type) }}</span> par {{ App\Models\User::find($todo->user_id)->name }}
                    <span title="{{ $todo->created_at }}">
                            {{ Illuminate\Support\Carbon::parse($todo->created_at)->diffForHumans() }}
                    </span>
                    <br/>
                    {{ $todo->subject }}
                </p>
                @empty
                <div class="flex justify-center items-center space-x-2">
                    <x-icon.inbox class="h-8 w-8 text-cool-gray-400" />
                    <span class="font-medium py-4 text-cool-gray-400 text-xl">{{ __('Nothing found...') }}</span>
                </div>
                @endforelse

            </div>
        </div>
        @endif
        <!-- End Connected Users //-->

    </div>

</div>