<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Notifications</h1>
            <p class="text-gray-500">All of your notifications in one place.</p>
        </div>
        @if($totals['all'] > 0)
            <button
                wire:click="clearAll"
                wire:confirm="Delete all of your notifications? This cannot be undone."
                class="self-start text-sm font-semibold text-gray-400 hover:text-rose-600 transition-colors"
            >Clear all</button>
        @endif
    </div>

    @php
        $tabs = [
            'all' => ['label' => 'All', 'count' => $totals['all']],
            'unread' => ['label' => 'Unread', 'count' => $totals['unread']],
            'read' => ['label' => 'Read', 'count' => $totals['read']],
        ];

        $typeStyles = [
            'obe_data_reminder' => ['icon' => 'fas fa-clipboard-check', 'bg' => 'bg-amber-100', 'text' => 'text-amber-700'],
            'leave_decision' => ['icon' => 'fas fa-file-signature', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
            'ah_leave_review' => ['icon' => 'fas fa-user-clock', 'bg' => 'bg-sky-100', 'text' => 'text-sky-700'],
            'admin_leave_review' => ['icon' => 'fas fa-user-shield', 'bg' => 'bg-sky-100', 'text' => 'text-sky-700'],
            'hr_leave_review' => ['icon' => 'fas fa-building', 'bg' => 'bg-sky-100', 'text' => 'text-sky-700'],
            'substitute_assignment' => ['icon' => 'fas fa-user-graduate', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
        ];
    @endphp

    <div class="mb-6 grid grid-cols-3 gap-3">
        @foreach($tabs as $key => $tab)
            <button
                type="button"
                wire:click="$set('filter', '{{ $key }}')"
                class="rounded-xl border p-4 text-left transition-all {{ $filter === $key ? 'border-indigo-300 bg-indigo-50 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider {{ $filter === $key ? 'text-indigo-700' : 'text-gray-500' }}">{{ $tab['label'] }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $filter === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $tab['count'] }}</span>
                </div>
                <div class="mt-1 text-2xl font-extrabold {{ $filter === $key ? 'text-indigo-900' : 'text-gray-900' }}">{{ $tab['count'] }}</div>
            </button>
        @endforeach
    </div>

    <div class="space-y-6">
        @php $grouped = $notifications->groupBy(function ($n) {
            $date = $n->created_at;
            if ($date->isToday()) return 'Today';
            if ($date->isYesterday()) return 'Yesterday';
            return $date->format('F j, Y');
        }); @endphp

        @forelse($grouped as $groupLabel => $group)
            <div>
                <h2 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">{{ $groupLabel }}</h2>
                <div class="space-y-2">
                    @foreach($group as $notification)
                        @php
                            $data = $notification->data;
                            $actionUrl = $data['action_url'] ?? $data['review_url'] ?? null;
                            $isUnread = is_null($notification->read_at);
                            $type = $data['type'] ?? 'default';
                            $style = $typeStyles[$type] ?? ['icon' => 'fas fa-bell', 'bg' => 'bg-gray-100', 'text' => 'text-gray-600'];
                        @endphp
                        <div class="flex items-start gap-3 rounded-xl border p-4 transition-shadow {{ $isUnread ? 'border-indigo-200 bg-indigo-50/40 shadow-sm' : 'border-gray-200 bg-white' }}" wire:key="notif-{{ $notification->id }}">
                            <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $style['bg'] }}">
                                <i class="{{ $style['icon'] }} {{ $style['text'] }} text-sm"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900">{{ $data['title'] ?? 'Update' }}</p>
                                        <p class="mt-0.5 text-sm text-gray-600">{{ $data['message'] ?? '' }}</p>
                                    </div>
                                    <span class="shrink-0 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-2 flex items-center gap-2">
                                    @if($actionUrl)
                                        <a href="{{ $actionUrl }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-bold text-white hover:bg-indigo-700">
                                            <i class="fas fa-arrow-right text-[10px]"></i> Open
                                        </a>
                                    @endif
                                    @if($isUnread)
                                        <button wire:click="markAsRead('{{ $notification->id }}')" class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-2.5 py-1 text-xs font-bold text-gray-600 hover:bg-gray-100">
                                            <i class="fas fa-check text-[10px]"></i> Mark read
                                        </button>
                                    @endif
                                    <button wire:click="deleteNotification('{{ $notification->id }}')" wire:confirm="Delete this notification?" class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold text-gray-300 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                </div>
                            </div>

                            @if($isUnread)
                                <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-indigo-500"></span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-24 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-sm">
                    <i class="fas fa-bell-slash text-2xl text-gray-300"></i>
                </div>
                <p class="text-gray-500 font-semibold">No notifications here</p>
                <p class="mt-1 text-sm text-gray-400">
                    @if($filter === 'unread')
                        You're all caught up — no unread notifications.
                    @elseif($filter === 'read')
                        No read notifications yet.
                    @else
                        When you receive updates, they will show up here.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
