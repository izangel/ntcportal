<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SystemUpdate;

class SystemUpdatesPage extends Component
{
    public $updates = [];
    public $selectedCategory = '';

    protected $queryString = ['selectedCategory' => ['except' => '']];

    public array $categories = ['New Feature', 'Bug Fix', 'Improvement'];

    public function mount(): void
    {
        // Sanitize the query-string value up front so no arbitrary text ever
        // reaches the view/JS even if the URL is crafted.
        if (! in_array($this->selectedCategory, $this->categories, true)) {
            $this->selectedCategory = '';
        }

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
        // Whitelist so a crafted URL value can never reach the view/JS as arbitrary text.
        if (! in_array($category, $this->categories, true)) {
            $category = '';
        }

        $this->selectedCategory = $category === $this->selectedCategory ? '' : $category;
        $this->loadUpdates();
    }

    public function clearCategory(): void
    {
        $this->selectedCategory = '';
        $this->loadUpdates();
    }

    public function render()
    {
        return view('livewire.system-updates-page', [
            'categories' => $this->categories,
        ])->extends('layouts.admin')
            ->section('content');
    }
}