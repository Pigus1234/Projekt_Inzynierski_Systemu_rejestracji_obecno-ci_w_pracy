<?php

namespace App\Http\Controllers;

use App\Models\Department;
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
        $departmentOptions = Department::query()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        return view('employees.create', [
            'departmentOptions' => $departmentOptions,
            'departmentSelectedValue' => '',
            'departmentSelectionHint' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rfid_uid' => ['required', 'string', 'max:255', 'unique:employees,rfid_uid'],
            'full_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255', 'exists:departments,name'],
        ]);

        Employee::query()->create($validated);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Pracownik został dodany.');
    }

    public function edit(Employee $employee): View
    {
        $departmentOptions = Department::query()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $currentDepartmentName = is_string($employee->department) ? trim($employee->department) : '';
        $currentDepartmentExists = $currentDepartmentName === '' || in_array($currentDepartmentName, $departmentOptions, true);

        $departmentSelectedValue = $currentDepartmentExists ? $currentDepartmentName : '';
        $departmentSelectionHint = $currentDepartmentExists || $currentDepartmentName === ''
            ? null
            : ('Aktualny dział „' . $currentDepartmentName . '” nie istnieje na liście. Wybierz dział ponownie.');

        return view('employees.edit', [
            'employee' => $employee,
            'departmentOptions' => $departmentOptions,
            'departmentSelectedValue' => $departmentSelectedValue,
            'departmentSelectionHint' => $departmentSelectionHint,
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
            'department' => ['nullable', 'string', 'max:255', 'exists:departments,name'],
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
