<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    /**
     * Sign-up form for customers.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Register a customer and sign them in.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->users->register($request->validated());

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('movies.index')
            ->with('status', 'Регистрация завершена. Добро пожаловать!');
    }
}
