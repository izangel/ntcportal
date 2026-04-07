<div x-data="{ show: false, message: '', type: 'success', timeout: null }"
     x-init="
        @this.on('show-toast', () => {
            show = true;
            clearTimeout(timeout);
            timeout = setTimeout(() => show = false, 5000); // Hide after 5 seconds
        });
     "
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-full"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-full"
     class="fixed bottom-5 right-5 z-50 max-w-xs w-full pointer-events-auto overflow-hidden rounded-lg shadow-xl"
     style="display: none;">

    <div :class="{
        'bg-green-600': @js($type) === 'success',
        'bg-red-600': @js($type) === 'error'
    }" class="p-4 text-white">
        <div class="flex items-start">
            <div class="flex-shrink-0 pt-0.5">
                <svg x-show="@js($type) === 'success'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="@js($type) === 'error'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.332 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="ml-3 flex-1 pt-0.5">
                <p class="text-sm font-medium leading-5">
                    {{ $message }}
                </p>
            </div>
            <div class="ml-4 flex flex-shrink-0">
                <button @click="show = false" class="inline-flex text-white transition ease-in-out duration-150 hover:text-gray-200 focus:outline-none focus:text-gray-200">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>