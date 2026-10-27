<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Sign-in form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Sign the user in and send them to the film catalogue.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Неверный email или пароль.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        /*
         * Every role can open the catalogue, so it is a safe landing page even
         * when the sign-in was triggered by a management URL.
         */
        return redirect()
            ->route('movies.index')
            ->with('status', "Вы вошли как {$user->role->label()}.");
    }

    /**
     * Sign the user out.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('movies.index')
            ->with('status', 'Вы вышли из панели.');
    }
}
