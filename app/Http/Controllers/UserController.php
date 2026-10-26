<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    /**
     * Accounts of the panel and their roles.
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('users.index', [
            'users' => User::query()
                ->orderBy('name')
                ->paginate(10),
            'roles' => UserRole::assignable(),
        ]);
    }

    /**
     * Form for a new staff account.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    /**
     * Store a new manager.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $this->users->createManager($request->validated());

        return redirect()
            ->route('users.index')
            ->with('status', "Менеджер «{$user->name}» создан.");
    }

    /**
     * Change the role of a customer or a manager.
     */
    public function update(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->users->changeRole($user, UserRole::from((string) $request->validated('role')));

        return redirect()
            ->route('users.index')
            ->with('status', "Роль пользователя «{$user->name}» изменена на «{$user->role->label()}».");
    }
}
