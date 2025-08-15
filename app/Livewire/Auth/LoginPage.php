<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Login')]
#[Layout('components.layouts.guest')]
class LoginPage extends Component
{
    use Toast;

    public string $email = '';
    public string $password = '';

    public function login()
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            session()->regenerate();
            $this->redirect('/selamat-datang', navigate: true);
            return;
        }

        $this->addError('email', 'Email atau password yang Anda masukkan salah.');
        $this->reset('password');
    }

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
