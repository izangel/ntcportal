<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SystemUpdate;

class SystemUpdatesPage extends Component
{
    public $updates = [];
    public $selectedCategory = '';

    protected $queryString = ['selectedCategory' => ['except' => '']];

    public array $filters = [];

    public function mount(): void
    {
        $this->loadUpdates();
    }

    public function loadUpdates(): void
    {
        $query = SystemUpdate::orderBy('release_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }

        $this->updates = $query->get();
    }

    public function setCategory(string $category): void
    {
        $this->selectedCategory = $category === $this->selectedCategory ? '' : $category;
        $this->loadUpdates();
    }

    public function render()
    {
        return view('livewire.system-updates-page', [
            'categories' => [
                'New Feature',
                'Bug Fix',
                'Improvement',
            ],
        ])->extends('layouts.admin')
            ->section('content');
    }
}