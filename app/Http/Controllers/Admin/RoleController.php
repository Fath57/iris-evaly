<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionModule;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        // Get modules with their permissions
        $modules = PermissionModule::with('permissions')
            ->orderBy('order')
            ->get()
            ->map(function ($module) {
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'slug' => $module->slug,
                    'description' => $module->description,
                    'icon' => $module->icon,
                    'permissions' => $module->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'description' => $permission->description,
                        ];
                    }),
                ];
            });

        return Inertia::render('Admin/Roles/Create', [
            'modules' => $modules,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->syncPermissions($validated['permissions']);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle créé avec succès');
    }

    public function edit(Role $role)
    {
        // Get modules with their permissions
        $modules = PermissionModule::with('permissions')
            ->orderBy('order')
            ->get()
            ->map(function ($module) {
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'slug' => $module->slug,
                    'description' => $module->description,
                    'icon' => $module->icon,
                    'permissions' => $module->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'description' => $permission->description,
                        ];
                    }),
                ];
            });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
            ],
            'modules' => $modules,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->syncPermissions($validated['permissions']);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle mis à jour avec succès');
    }

    public function destroy(Role $role)
    {
        // Prevent deletion of super-admin role
        if ($role->name === 'super-admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Impossible de supprimer le rôle super-admin');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle supprimé avec succès');
    }
}
