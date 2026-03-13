<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\SystemUpdate;

class SystemUpdateList extends Component
{
    public function render()
    {
        $updates = SystemUpdate::orderBy('release_date', 'desc')->get();
        return view('livewire.user.system-update-list', [
            'updates' => $updates
        ])->layout('layouts.admin'); // Using admin layout which seems to be the main layout for authenticated users
    }
}
