<x-nav-link :route="'dashboard'" :icon="'dashboard'">
    {{ __('Dashboard') }}
</x-nav-link>

@can('manage-users')
<x-nav-link :route="'users'" :icon="'users'">
    {{ __('Users') }}
</x-nav-link>
@endcan

<x-nav-link :route="'login'" :icon="'stop'">
    {{ __('Missions') }}
</x-nav-link>

<x-nav-link :route="'login'" :icon="'stop'">
    {{ __('Mission expenses') }}
</x-nav-link>

<x-nav-link :route="'login'" :icon="'stop'">
    {{ __('Non-mission purchases') }}
</x-nav-link>

<x-nav-link :route="'login'" :icon="'stop'">
    {{ __('Purchase orders') }}
</x-nav-link>

{{ $slot }}

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <x-nav-link :route="'logout'" :icon="'logout'"
            onclick="event.preventDefault(); this.closest('form').submit();">
        {{ __('Log Out') }}
    </x-nav-link>
</form>
