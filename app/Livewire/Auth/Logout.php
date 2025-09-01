<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        // Inline blade view
        return <<<'HTML'
        <x-button label="Logout" icon="o-arrow-left-on-rectangle" wire:click="logout" class="btn-sm btn-outline btn-error" responsive />
        HTML;
    }
}
