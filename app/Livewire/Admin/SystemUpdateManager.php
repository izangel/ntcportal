<?php

namespace App\Livewire\Admin;

                        use Livewire\Component;
use App\Models\SystemUpdate;
use Illuminate\Support\Facades\Auth;

class SystemUpdateManager extends Component
{
    public $version_number;
    public $category = 'New Feature';
    public $title;
    public $release_date;
    public $description;
    public $updateId;
    public $isModalOpen = false;

    protected $rules = [
        'version_number' => 'nullable|string|max:20',
        'category' => 'required|string',
        'title' => 'required|string|max:255',
        'release_date' => 'required|date',
        'description' => 'required|string',
    ];

    public function mount()
    {
        $this->release_date = date('Y-m-d');
    }

    public function render()
    {
        $updates = SystemUpdate::orderBy('release_date', 'desc')->get();
        return view('livewire.admin.system-update-manager', [
            'updates' => $updates
        ])->layout('layouts.admin');
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->reset(['version_number', 'category', 'title', 'release_date', 'description', 'updateId']);
        $this->release_date = date('Y-m-d');
    }

    public function save()
    {
        $this->validate();

        SystemUpdate::updateOrCreate(
            ['id' => $this->updateId],
            [
                'version_number' => $this->version_number,
                'category' => $this->category,
                'title' => $this->title,
                'release_date' => $this->release_date,
                'description' => $this->description,
            ]
        );

        session()->flash('message', $this->updateId ? 'Update updated successfully.' : 'Update created successfully.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $update = SystemUpdate::findOrFail($id);
        $this->updateId = $update->id;
        $this->version_number = $update->version_number;
        $this->category = $update->category;
        $this->title = $update->title;
        $this->release_date = $update->release_date;
        $this->description = $update->description;
        $this->openModal();
    }

    public function delete($id)
    {
        SystemUpdate::destroy($id);
        session()->flash('message', 'Update deleted successfully.');
    }
}
