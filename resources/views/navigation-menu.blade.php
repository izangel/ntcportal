<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    
                  

                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">

                 {{-- Notification Icon with Count + Dropdown --}}
                @if(Auth::check())
                    @php
                        $notifications = Auth::user()->notifications()->latest()->take(8)->get();
                        $unreadNotificationsCount = Auth::user()->unreadNotifications->count();
                    @endphp
                    <div class="relative mr-4" x-data="{ notifOpen: false }">
                        <button type="button" @click="notifOpen = !notifOpen" class="relative text-gray-600 hover:text-gray-900 focus:outline-none" aria-label="Notifications">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.007 2.007 0 0118 14.595V10a6 6 0 00-12 0v4.595a2.007 2.007 0 01-1.405 1.405L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"></path>
                            </svg>

                            @if($unreadNotificationsCount > 0)
                                <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">
                                    {{ $unreadNotificationsCount }}
                                </span>
                            @endif
                        </button>

                        <div x-show="notifOpen" x-cloak x-transition x-on:click.outside="notifOpen = false"
                            class="absolute right-0 mt-3 w-96 max-w-[90vw] rounded-xl bg-white shadow-xl ring-1 ring-black/5 border border-gray-100 overflow-hidden z-50">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">🔔 Notifications</h4>
                                <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $unreadNotificationsCount }} new</span>
                            </div>

                            <div class="max-h-96 overflow-y-auto divide-y divide-gray-50">
                                @forelse($notifications as $notification)
                                    @php
                                        $data = $notification->data;
                                        $actionUrl = $data['action_url'] ?? $data['review_url'] ?? null;
                                        $isUnread = is_null($notification->read_at);
                                        $isObe = ($data['type'] ?? '') === 'obe_data_reminder';
                                    @endphp
                                    <div class="p-3 {{ $isUnread ? 'bg-indigo-50/40' : 'bg-white' }} hover:bg-gray-50 transition relative group">
                                        @if($isUnread)
                                            <span class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></span>
                                        @endif
                                        <div class="flex items-start justify-between gap-2 pl-1">
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-gray-900 truncate">
                                                    @if($isObe)
                                                        <i class="fas fa-clipboard-check text-amber-500 mr-1"></i>
                                                    @endif
                                                    {{ $data['title'] ?? 'Update' }}
                                                </p>
                                                <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-2">{{ $data['message'] ?? '' }}</p>
                                                <p class="text-[9px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        @if($actionUrl || $isUnread)
                                            <div class="mt-2 flex items-center gap-2 pl-1">
                                                @if($actionUrl)
                                                    <a href="{{ $actionUrl }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-600 text-white px-2 py-1 text-[10px] font-bold hover:bg-indigo-700">
                                                        <i class="fas fa-arrow-right"></i> Open
                                                    </a>
                                                @endif
                                                @if($isUnread)
                                                    <form method="POST" action="{{ route('notifications.markAsRead', $notification->id) }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 rounded-md border border-gray-300 text-gray-600 px-2 py-1 text-[10px] font-bold hover:bg-gray-100">
                                                            <i class="fas fa-check"></i> Mark read
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 text-center py-8 italic">All caught up!</p>
                                @endforelse
                            </div>

                            <div class="px-4 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                                <a href="{{ route('notifications.index') }}" class="text-[11px] font-bold text-indigo-600 hover:underline">View all</a>
                                @if($unreadNotificationsCount > 0)
                                    <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-bold text-gray-500 hover:text-gray-800">Mark all as read</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                            </button>
                        @else
                            <span class="inline-flex rounded-md">
                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                    {{ Auth::user()->name }}

                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </span>
                        @endif
                    </x-slot>

                    <x-slot name="content">
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Manage Account') }}
                        </div>

                        <x-dropdown-link href="{{ route('profile.show') }}">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                {{ __('API Tokens') }}
                            </x-dropdown-link>
                        @endif

                        <div class="border-t border-gray-200"></div>

                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}"
                                     @click.prevent="$root.submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

           

        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 mr-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}"
                                   @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>