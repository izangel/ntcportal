<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Load Manager - Full Screen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 antialiased">
    <div class="flex-1 flex flex-col">
                {{-- Navigation Menu (Top Bar) --}}
                <header class="bg-white shadow-sm sticky top-0 z-50">
                    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                        @livewire('navigation-menu')
                    </div>
                </header>

                @if (isset($header))
                    <header class="bg-white shadow-sm border-b border-gray-200">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                                {{ $header }}
                            </h2>
                        </div>
                    </header>
                @endif
                <main>
                    
                    @yield('content') {{-- For Livewire 2 --}}
                </main>
     </div>
    @livewireScripts
</body>
</html>