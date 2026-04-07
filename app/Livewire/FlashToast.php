<?php

namespace App\Livewire;

use Livewire\Component;

class FlashToast extends Component
{
    public $message = '';
    public $type = 'success'; // 'success' or 'error'

    // This listener is triggered by the parent component (FacultyCourseBlockView)
    // after it processes the message and sets the session flash data.
    public function show($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
        
        // Emit an event that JavaScript will listen for to show the toast
        $this->dispatch('show-toast');
    }

    public function render()
    {
        return view('livewire.flash-toast');
    }
}