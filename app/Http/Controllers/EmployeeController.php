<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Helper\ResponseHelper;
use App\Http\Resources\EmployeeResource;
use App\Traits\HandlesTransactions;

class EmployeeController extends Controller
{
    use HandlesTransactions;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::with(['department', 'designation'])->get();
        if ($employees->isEmpty()) {
            return ResponseHelper::error("No employees found", null, statusCode: 404);
        }
        return ResponseHelper::success("employees retrieved successfully", EmployeeResource::collection($employees));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {
        return $this->executeInTransaction(function () use ($request) {
            $employee = Employee::create($request->validated());
            return ResponseHelper::success('Employee Created', EmployeeResource::make($employee->load(['department', 'designation'])), 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show($employeeId)
    {
        $employee = Employee::with(['department', 'designation'])->findOrFail($employeeId);
        return ResponseHelper::success('Employee found', new EmployeeResource($this->loadRelations($employee)), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, $id)
    {
        return $this->executeInTransaction(function () use ($request, $id) {
            $employee = Employee::findOrFail($id);
            $employee->update($request->validated());
            return ResponseHelper::success('Employee updated', new EmployeeResource($this->loadRelations($employee->fresh())), 200);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($employeeId)
    {
        return $this->executeInTransaction(function () use ($employeeId) {
            $employee = Employee::findOrFail($employeeId);

            // Check if deletable
            if ($this->isDeletable($employee)) {
                $employee->delete();
                return ResponseHelper::success('Employee deleted', $employee, 200);
            }

            return ResponseHelper::error(
                'Cannot delete - Employee is in use',
                ['references' => $this->getReferences($employee)],
                409
            );
        });
    }

    protected function isDeletable(Employee $employee): bool
    {
        // Example checks (customize based on your relationships)
        return !$employee->users()->exists();
    }

    protected function getReferences(Employee $employee): array
    {
        return [
            'user_count' => $employee->users()->count()
        ];
    }

    protected function loadRelations(Employee $employee): Employee
    {
        return $employee->load(['department', 'designation']);
    }
}
