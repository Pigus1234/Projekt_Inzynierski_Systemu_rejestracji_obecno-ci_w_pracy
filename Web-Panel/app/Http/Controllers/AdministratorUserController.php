<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdministratorUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['role', 'permissions'])
            ->orderBy('name')
            ->paginate(15);

        return view('administrator.users.index', [
            'users' => $users,
        ]);
    }

    public function create(Request $request): View
    {
        return view('administrator.users.create', [
            'roles' => $this->rolesAvailableForUser($request->user()),
            'permissions' => Permission::query()->orderBy('label')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $this->ensureAdministratorRoleCannotBeAssignedByNonAdministrator($request->user(), (int) $validated['role_id']);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => (int) $validated['role_id'],
        ]);

        $user->permissions()->sync($validated['permission_ids'] ?? []);

        return redirect()
            ->route('administrator.users.index')
            ->with('success', 'Użytkownik został utworzony.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->ensureAdministratorAccountCannotBeManagedByNonAdministrator($request->user(), $user);

        $user->load(['role', 'permissions']);

        return view('administrator.users.edit', [
            'user' => $user,
            'roles' => $this->rolesAvailableForUser($request->user()),
            'permissions' => Permission::query()->orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdministratorAccountCannotBeManagedByNonAdministrator($request->user(), $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $this->ensureAdministratorRoleCannotBeAssignedByNonAdministrator($request->user(), (int) $validated['role_id']);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => (int) $validated['role_id'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);
        $user->permissions()->sync($validated['permission_ids'] ?? []);

        return redirect()
            ->route('administrator.users.index')
            ->with('success', 'Użytkownik został zaktualizowany.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdministratorAccountCannotBeManagedByNonAdministrator($request->user(), $user);

        if ((int) auth()->id() === (int) $user->id) {
            return redirect()
                ->route('administrator.users.index')
                ->with('error', 'Nie możesz usunąć aktualnie zalogowanego konta.');
        }

        $user->delete();

        return redirect()
            ->route('administrator.users.index')
            ->with('success', 'Użytkownik został usunięty.');
    }

    private function rolesAvailableForUser(?User $user)
    {
        $query = Role::query()->orderBy('name');

        if (! $this->isAdministrator($user)) {
            $query->where('name', '!=', 'Administrator');
        }

        return $query->get();
    }

    private function ensureAdministratorAccountCannotBeManagedByNonAdministrator(?User $actingUser, User $targetUser): void
    {
        if ($targetUser->role?->name === 'Administrator' && ! $this->isAdministrator($actingUser)) {
            abort(403);
        }
    }

    private function ensureAdministratorRoleCannotBeAssignedByNonAdministrator(?User $actingUser, int $roleId): void
    {
        $administratorRoleId = (int) Role::query()->where('name', 'Administrator')->value('id');

        if ($administratorRoleId > 0 && $roleId === $administratorRoleId && ! $this->isAdministrator($actingUser)) {
            abort(403);
        }
    }

    private function isAdministrator(?User $user): bool
    {
        return (bool) $user && $user->role?->name === 'Administrator';
    }
}
