<x-layouts.base>
    <div class="h-screen flex overflow-hidden bg-cool-gray-100" x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false">
        <!-- Off-canvas menu for mobile -->
        <div x-show="sidebarOpen" class="md:hidden" style="display: none;">
            <div class="fixed inset-0 flex z-40">
                <div @click="sidebarOpen = false" x-show="sidebarOpen" x-description="Off-canvas menu overlay, show/hide based on off-canvas menu state." x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0" style="display: none;">
                    <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
                </div>
                <div x-show="sidebarOpen" x-description="Off-canvas menu, show/hide based on off-canvas menu state." x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex-1 flex flex-col max-w-xs w-full bg-indigo-800" style="display: none;">
                    <div class="absolute top-0 right-0 -mr-14 p-1">
                        <button x-show="sidebarOpen" @click="sidebarOpen = false" class="flex items-center justify-center h-12 w-12 rounded-full focus:outline-none focus:bg-gray-600" aria-label="Close sidebar" style="display: none;">
                            <svg class="h-6 w-6 text-white" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                        <div class="flex-shrink-0 flex items-center px-4">
                            <x-application-logo-light class="h-6 w-auto" />
                        </div>
                        <nav class="mt-5 px-2 space-y-1">

                            <x-nav-link :route="'dashboard'" :icon="'dashboard'">
                                {{ __('Dashboard') }}
                            </x-nav-link>

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

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-nav-link :route="'logout'" :icon="'logout'"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-nav-link>
                            </form>

                        </nav>
                    </div>
                    <div class="flex-shrink-0 flex border-t border-indigo-700 p-4">
                        <a href="/profile" class="flex-shrink-0 group block focus:outline-none">
                            <div class="flex items-center">
                                <div>
                                    <img class="inline-block h-10 w-10 rounded-full" src="{{ auth()->user()->avatarUrl() }}" alt="Profile Photo">
                                </div>
                                <div class="ml-3">
                                    <p class="text-base leading-6 font-medium text-white">
                                        {{ Auth::user()->name }}
                                    </p>
                                    <p class="text-sm leading-5 font-medium text-indigo-300 group-hover:text-indigo-100 group-focus:underline transition ease-in-out duration-1000">
                                        {{ __('View profile') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="flex-shrink-0 w-14">
                    <!-- Force sidebar to shrink to fit close icon -->
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="hidden md:flex md:flex-shrink-0">   
            <div class="flex flex-col w-64 border-r border-gray-200 bg-indigo-800">
                <div class="h-0 flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                    <div class="flex items-center flex-shrink-0 px-4">
                        <x-application-logo-light class="h-7 w-auto"/>
                    </div>
                    <!-- Sidebar component, swap this element with another sidebar if you like -->
                    <nav class="mt-5 space-y-1 flex-1 px-2 bg-indigo-800">
                        <x-nav-link :route="'dashboard'" :icon="'dashboard'">
                            {{ __('Dashboard') }}
                        </x-nav-link>

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

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-nav-link :route="'logout'" :icon="'logout'"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-nav-link>
                        </form>

                    </nav>
                </div>

                <div class="flex-shrink-0 flex border-t border-indigo-700 p-4">
                    <a href="/profile" class="flex-shrink-0 w-full group block">
                        <div class="flex items-center">
                            <div>
                                <img class="inline-block h-9 w-9 rounded-full" src="{{ auth()->user()->avatarUrl() }}" alt="Profile Photo">
                            </div>

                            <div class="ml-3">
                                <p class="text-sm leading-5 font-medium text-white">
                                    {{ Auth::user()->name }}
                                </p>

                                <p class="text-xs leading-4 font-medium text-indigo-300 group-hover:text-indigo-100 transition ease-in-out duration-1000">
                                    {{ __('View profile') }}
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex flex-col w-0 flex-1 overflow-hidden">
            <div class="md:hidden pl-1 pt-1 sm:pl-3 sm:pt-3">
                <button @click.stop="sidebarOpen = true" class="-ml-0.5 -mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:bg-gray-200 transition ease-in-out duration-1000" aria-label="Open sidebar">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <main class="flex-1 relative z-0 overflow-y-auto pt-2 pb-6 focus:outline-none md:py-6" tabindex="0" x-data="" x-init="$el.focus()">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">

                    {{ $slot }}

                </div>
            </main>
        </div>


    </div>
</x-layouts.base>
