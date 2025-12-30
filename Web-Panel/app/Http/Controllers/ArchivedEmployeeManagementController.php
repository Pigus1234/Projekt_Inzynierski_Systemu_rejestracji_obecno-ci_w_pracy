<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArchivedEmployeeManagementController extends Controller
{
    public function index(): View
    {
        $employees = Employee::onlyTrashed()
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('employees.archived', [
            'employees' => $employees,
        ]);
    }

    public function restore(int $employeeId): RedirectResponse
    {
        $employee = Employee::onlyTrashed()->findOrFail($employeeId);
        $employee->restore();

        return redirect()
            ->route('employees.archived')
            ->with('success', 'Pracownik został przywrócony.');
    }
}
