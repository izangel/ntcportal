<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationsPage extends Component
{
    use WithPagination;

    public $filter = 'all';

    public function markAsRead($notificationId): void
    {
        $notification = Auth::user()->notifications()->whereKey($notificationId)->first();
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function deleteNotification($notificationId): void
    {
        Auth::user()->notifications()->whereKey($notificationId)->first()?->delete();
    }

    public function clearAll(): void
    {
        Auth::user()->notifications()->delete();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Auth::user()->notifications()->latest();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(15);

        $totals = [
            'all' => Auth::user()->notifications()->count(),
            'unread' => Auth::user()->unreadNotifications()->count(),
            'read' => Auth::user()->readNotifications()->count(),
        ];

        return view('livewire.notifications-page', [
            'notifications' => $notifications,
            'totals' => $totals,
        ])->extends('layouts.admin')->section('content');
    }
}
