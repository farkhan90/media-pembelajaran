<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class SelamatDatangPage extends Component
{
    // Tidak perlu properti atau metode apa pun lagi
    // Komponen ini menjadi 'stateless'
    public bool $petunjukModal = false;
    public function render()
    {
        return view('livewire.selamat-datang-page');
    }
}
