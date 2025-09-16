<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse; // <-- Penting untuk type-hint

class AuthenticatedSessionController extends Controller
{
    /**
     * Hancurkan sesi otentikasi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Redirect ke halaman welcome
        return redirect('/');
    }
}
