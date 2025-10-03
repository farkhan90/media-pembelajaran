<?php

namespace App\Livewire\Pembelajaran;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class PetualanganSelesai extends Component
{
    public function render()
    {
        return view('livewire.pembelajaran.petualangan-selesai');
    }
}
