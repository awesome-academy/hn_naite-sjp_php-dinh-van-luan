<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Constants\UserRoles;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    protected array $roleBadges;
    protected array $defaultBadge;
    protected int $perPage;

    public function __construct()
    {
        $this->middleware('can:manage-users');

        $this->roleBadges = config('roles.badges');
        $this->defaultBadge = config('roles.default');
        $this->perPage = config("paginate")["per_page"] ?? 10;
    }

    public function index(Request $request)
    {
        $query = User::query()->select('id', 'name', 'email', 'premium_expired_at')->with(['roles:id,name']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        $users = tap($query->paginate($this->perPage))->withQueryString();

        $roleBadges = $this->roleBadges;
        $defaultBadge = $this->defaultBadge;

        return view('admin.user-management.index', compact('users', 'roleBadges', 'defaultBadge'));
    }

    public function show(User $user)
    {
        $user->load('roles');

        $roleBadges = $this->roleBadges;
        $defaultBadge = $this->defaultBadge;

        return view('admin.user-management.show', compact('user', 'roleBadges', 'defaultBadge'));
    }

    public function edit(User $user)
    {
        $user->load('roles');

        $roleBadges = $this->roleBadges;
        $defaultBadge = $this->defaultBadge;

        return view('admin.user-management.edit', compact('user', 'roleBadges', 'defaultBadge'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
           'username' => ['required', 'string', 'max:255'],
           'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
           'role' => ['required', 'string', Rule::in(UserRoles::ALL)],
           'premium_expired_at' => ['nullable', 'date'],
        ]);

        $premiumPermanent = $request->boolean('premium_permanent', false);

        $user->update([
            'name' => $validated['username'],
            'email' => $validated['email'],
            'premium_expired_at' => $request->input('role') === UserRoles::PREMIUM_USER
                ? ($premiumPermanent ? null : $validated['premium_expired_at'])
                : null
        ]);

        $user->roles()->sync(
            [$this->getRoleIdByName($validated['role'])]
        );

        return redirect()->route('users.index')->with('success', __('user.updated_success'));
    }

    protected function getRoleIdByName(string $roleName): int
    {
        $roleId = Role::where('name', $roleName)->value('id');

        if (!$roleId) {
            abort(400, __('user.invalid_role'));
        }

        return $roleId;
    }

    public function destroy(User $user)
    {
        if ($user->hasRole(UserRoles::ADMIN)) {
            return redirect()->route('users.index')
                ->with('error', __('user.delete_error_admin'));
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', __('user.deleted_success'));
    }
}
