<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::query()
            ->with('head:id,full_name,name,email')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.departments.index', ['departments' => $departments]);
    }

    public function edit(Department $department): View
    {
        $users = User::query()
            ->where('department_id', $department->id)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->orderBy('email')
            ->get(['id', 'name', 'full_name', 'email']);

        return view('admin.departments.edit', [
            'department' => $department,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'head_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('department_id', $department->id)->where('is_active', true)),
            ],
        ]);

        return DB::transaction(function () use ($data, $department) {
            $previousHeadId = $department->head_user_id;
            $newHeadId = $data['head_user_id'] ?? null;

            $department->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'head_user_id' => $newHeadId,
            ]);

            if ($previousHeadId && (int) $previousHeadId !== (int) $newHeadId) {
                $this->syncRoleAfterHeadRemoved((int) $previousHeadId);
            }

            if ($newHeadId) {
                $this->promoteToDepartmentHead((int) $newHeadId);
            }

            return redirect()->route('admin.departments.index')->with('status', 'Отдел обновлён');
        });
    }

    private function syncRoleAfterHeadRemoved(int $userId): void
    {
        $user = User::query()->find($userId);
        if (! $user || $user->isAdmin()) {
            return;
        }

        if (Department::query()->where('head_user_id', $userId)->exists()) {
            return;
        }

        if ($user->role === 'department_head') {
            $user->update(['role' => 'user']);
        }
    }

    private function promoteToDepartmentHead(int $userId): void
    {
        $user = User::query()->find($userId);
        if (! $user || $user->isAdmin()) {
            return;
        }

        if ($user->role !== 'department_head') {
            $user->update(['role' => 'department_head']);
        }
    }
}
