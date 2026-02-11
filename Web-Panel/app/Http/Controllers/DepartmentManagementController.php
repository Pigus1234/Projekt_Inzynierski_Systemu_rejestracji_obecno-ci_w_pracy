<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentManagementController extends Controller
{
    public function index(Request $request): View
    {
        $departments = Department::query()
            ->orderBy('name')
            ->paginate(15);

        return view('departments.index', [
            'departments' => $departments,
        ]);
    }

    public function create(Request $request): View
    {
        return view('departments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
        ]);

        Department::query()->create([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Dział został utworzony.');
    }

    public function edit(Request $request, Department $department): View
    {
        return view('departments.edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->ignore($department->id),
            ],
        ]);

        $department->update([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Dział został zaktualizowany.');
    }

    public function destroy(Request $request, Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()
            ->route('departments.index')
            ->with('success', 'Dział został usunięty.');
    }
}
