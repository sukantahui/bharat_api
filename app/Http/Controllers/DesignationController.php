<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $designations = Designation::all();
        return \App\Helper\ResponseHelper::success("Designations retrieved successfully", \App\Http\Resources\DesignationResource::collection($designations));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

   

    /**
     * Display the specified resource.
     */
    public function show(Designation $designation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Designation $designation)
    {
        //
    }

   
    public function destroy(Designation $designation)
    {
        //
    }
}
