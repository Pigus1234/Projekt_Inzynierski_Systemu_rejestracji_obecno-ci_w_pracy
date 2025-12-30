<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeManagementController extends Controller
{
    public function index(): View
    {
        $employees = Employee::query()
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
        ]);
    }

    public function create(): View
    {
        return view('employees.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rfid_uid' => ['required', 'string', 'max:255', 'unique:employees,rfid_uid'],
            'full_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        Employee::query()->create($validated);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Pracownik został dodany.');
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', [
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'rfid_uid' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees', 'rfid_uid')->ignore($employee->id),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $employee->update($validated);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Pracownik został zaktualizowany.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Pracownik został zarchiwizowany.');
    }
}
