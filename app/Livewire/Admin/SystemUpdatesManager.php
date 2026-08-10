<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\SystemUpdate;

class SystemUpdatesManager extends Component
{
    public $updates = [];
    public $editingId = null;
    public $title = '';
    public $category = 'New Feature';
    public $description = '';
    public $release_date = '';
    public $isModalOpen = false;
    public $confirmDeleteId = null;

    protected $rules = [
        'title' => 'required|string|max:255',
        'category' => 'required|string|in:New Feature,Bug Fix,Improvement',
        'description' => 'required|string',
        'release_date' => 'required|date',
    ];

    public function mount(): void
    {
        $this->release_date = now()->format('Y-m-d');
        $this->loadUpdates();
    }

    public function loadUpdates(): void
    {
        $this->updates = SystemUpdate::orderBy('release_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function openCreate(): void
    {
        $this->resetFields();
        $this->isModalOpen = true;
    }

    public function openEdit(int $id): void
    {
        $update = SystemUpdate::findOrFail($id);

        $this->editingId = $update->id;
        $this->title = $update->title;
        $this->category = $update->category;
        $this->description = $update->description;
        $this->release_date = $update->release_date?->format('Y-m-d');
        $this->isModalOpen = true;
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function resetFields(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->category = 'New Feature';
        $this->description = '';
        $this->release_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();

        SystemUpdate::updateOrCreate(
            ['id' => $this->editingId],
            [
                'title' => $this->title,
                'category' => $this->category,
                'description' => $this->description,
                'release_date' => $this->release_date,
            ]
        );

        session()->flash('system-update-message', $this->editingId
            ? 'System update updated successfully.'
            : 'System update published successfully.');

        $this->closeModal();
        $this->loadUpdates();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    public function delete(): void
    {
        if ($this->confirmDeleteId) {
            SystemUpdate::findOrFail($this->confirmDeleteId)->delete();
            session()->flash('system-update-message', 'System update deleted successfully.');
            $this->confirmDeleteId = null;
            $this->loadUpdates();
        }
    }

    public function render()
    {
        return view('livewire.admin.system-updates-manager', [
            'categories' => SystemUpdate::CATEGORIES,
        ])->extends('layouts.admin')
            ->section('content');
    }
}