<x-nav-link :route="'dashboard'" :icon="'dashboard'">
    {!! __('Dashboard') !!}
</x-nav-link>

<!-- User section //-->

<x-nav-link :route="'missions'" :icon="'stop'">
    {!! __('Missions') !!}
</x-nav-link>

<x-nav-link :route="'purchases'" :icon="'purchase'">
    {!! __('Non-mission purchases') !!}
</x-nav-link>

<x-nav-link :route="'orders'" :icon="'orderlist'">
    {!! __('Purchase orders') !!}
</x-nav-link>

{{ $slot }}

@can('manage-users')
<hr class="w-1/2 mx-1/4 opacity-60" />

<!-- Admin section //-->

<x-nav-link :route="'users'" :icon="'users'">
    {!! __('Users') !!}
</x-nav-link>

<x-nav-link :route="'institutions'" :icon="'institution'">
    {!! __('Institutions') !!}
</x-nav-link>

@endcan

<hr class="w-1/2 mx-1/4 opacity-60" />

<!-- Logout //-->

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <x-nav-link :route="'logout'" :icon="'logout'"
            onclick="event.preventDefault(); this.closest('form').submit();">
        {{ __('Log Out') }}
    </x-nav-link>
</form>
