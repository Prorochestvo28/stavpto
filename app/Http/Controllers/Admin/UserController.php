<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->with('department')->orderBy('full_name')->orderBy('email');

        if (! $request->user()->isAdmin()) {
            $dept = $request->user()->headedDepartment();
            abort_unless($dept, 403);
            $query->where('department_id', $dept->id);
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function create(Request $request): View
    {
        $departments = $this->departmentsForForm($request->user());

        return view('admin.users.create', ['departments' => $departments]);
    }

    public function store(Request $request): RedirectResponse
    {
        $admin = $request->user()->isAdmin();
        $deptHead = $request->user()->headedDepartment();

        $departmentRule = ['required', 'integer', 'exists:departments,id'];
        if (! $admin) {
            abort_unless($deptHead, 403);
            $departmentRule[] = Rule::in([$deptHead->id]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'department_id' => $departmentRule,
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', Rule::in($admin ? ['user', 'admin'] : ['user'])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (! $admin) {
            $data['role'] = 'user';
            $data['department_id'] = $deptHead->id;
        }

        User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'full_name' => $data['full_name'] ?? null,
            'position' => $data['position'] ?? null,
            'department_id' => $data['department_id'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Пользователь создан');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeEdit($request->user(), $user);

        $departments = $this->departmentsForForm($request->user());

        return view('admin.users.edit', [
            'editUser' => $user,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeEdit($request->user(), $user);

        $admin = $request->user()->isAdmin();
        $deptHead = $request->user()->headedDepartment();

        $departmentRule = ['required', 'integer', 'exists:departments,id'];
        if (! $admin) {
            $departmentRule[] = Rule::in([$deptHead->id]);
        }

        $editingSelf = (int) $user->id === (int) $request->user()->id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'position' => $editingSelf ? ['prohibited'] : ['nullable', 'string', 'max:255'],
            'department_id' => $departmentRule,
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', Rule::in($admin ? ['user', 'admin', 'department_head'] : ['user'])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (! $admin) {
            $data['role'] = 'user';
            $data['department_id'] = $deptHead->id;
        }

        if ($admin && ($data['role'] ?? '') === 'department_head') {
            if (! Department::query()->where('head_user_id', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'role' => ['Роль «Начальник отдела» назначается только при выборе главы отдела в разделе «Отделы».'],
                ]);
            }
        }

        $originalDeptId = (int) $user->department_id;

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'full_name' => $data['full_name'] ?? null,
            'department_id' => $data['department_id'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'is_active' => $request->user()->isAdmin()
                ? $request->boolean('is_active')
                : $user->is_active,
        ]);

        if (! $editingSelf) {
            $user->position = $data['position'] ?? null;
        }

        if ($admin) {
            $deptChanged = (int) $data['department_id'] !== $originalDeptId;
            if ($deptChanged || $data['role'] !== 'department_head') {
                Department::query()->where('head_user_id', $user->id)->update(['head_user_id' => null]);
            }
            if ($deptChanged && $user->role === 'department_head') {
                $user->role = 'user';
            }
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'Данные пользователя обновлены');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeEdit($request->user(), $user);

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Нельзя удалить собственную учётную запись.']);
        }

        if ($user->isAdmin() && User::query()->where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['user' => 'Нельзя отключить последнего администратора.']);
        }

        Department::query()->where('head_user_id', $user->id)->update(['head_user_id' => null]);

        $payload = ['is_active' => false];
        if ($user->role === 'department_head') {
            $payload['role'] = 'user';
        }

        $user->update($payload);

        return redirect()->route('admin.users.index')->with('status', 'Пользователь отключён');
    }

    public function restore(Request $request, User $user): RedirectResponse
    {
        $this->authorizeEdit($request->user(), $user);

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Нельзя изменить собственную учётную запись этим действием.']);
        }

        if ($user->is_active) {
            return redirect()->route('admin.users.index')->with('status', 'Пользователь уже активен.');
        }

        $user->update(['is_active' => true]);

        return redirect()->route('admin.users.index')->with('status', 'Пользователь снова включён.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Department>
     */
    private function departmentsForForm(User $actor): \Illuminate\Database\Eloquent\Collection
    {
        if ($actor->isAdmin()) {
            return Department::query()->orderBy('name')->get();
        }

        $dept = $actor->headedDepartment();
        abort_unless($dept, 403);

        return Department::query()->where('id', $dept->id)->get();
    }

    private function authorizeEdit(User $actor, User $target): void
    {
        if ($actor->isAdmin()) {
            return;
        }

        $dept = $actor->headedDepartment();
        abort_unless($dept && $target->department_id === $dept->id, 403);
        abort_if($target->isAdmin(), 403);
        abort_if($target->isDepartmentHead() && $target->id !== $actor->id, 403);
    }
}
