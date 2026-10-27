<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::all();
        if ($departments->isEmpty()) {
            return ResponseHelper::error("No departments found", null, 404);
        }
        return ResponseHelper::success("Departments retrieved successfully",DepartmentResource::collection($departments));
    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request)
    {
    
        $department = Department::create($request->validated());

        if ($department) {
            return ResponseHelper::success("Department created successfully", new DepartmentResource($department),201);
        } else {
            return ResponseHelper::error("Failed to create department", null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $dept = Department::findOrFail($request->id);
        // return $request->validated();
        $dept->update($request->validated());
        return $dept;


    //     Department::where('id', $request->id)->update([
    //     'department_name' => $request->departmentName,
    // ]);
    //    return $request->id;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        //
    }
}
