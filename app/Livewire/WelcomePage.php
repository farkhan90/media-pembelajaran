<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class WelcomePage extends Component
{
    public bool $infoModal = false;
    public bool $petunjukModal = false;
    public bool $pengembangModal = false;

    public function render()
    {
        return view('livewire.welcome-page');
    }
}