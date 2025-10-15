<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;

class UserController extends Controller
{
    protected UserService $userService;
    protected InvitationService $invitationService;

    public function __construct(UserService $userService, InvitationService $invitationService)
    {
        $this->userService = $userService;
        $this->invitationService = $invitationService;
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'role' => $request->input('role'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
        ];

        $users = $this->userService->getUsersPaginated(15, $filters);
        $pendingInvitations = $this->invitationService->getPendingInvitations();
        $roles = Role::whereNotIn('name', ['student'])->get(['id', 'name', 'description']);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'pendingInvitations' => $pendingInvitations,
            'roles' => $roles,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $roles = Role::whereNotIn('name', ['student'])->get(['id', 'name', 'description']);

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $this->userService->createUser($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

    public function edit($id)
    {
        $user = $this->userService->findUser($id);
        $roles = Role::whereNotIn('name', ['student'])->get(['id', 'name', 'description']);
        $userRoles = $user->roles->pluck('name')->toArray();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $this->userService->updateUser($id, $validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès');
    }

    public function destroy($id)
    {
        $this->userService->deleteUser($id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès');
    }
}
