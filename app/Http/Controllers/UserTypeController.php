<?php

namespace App\Http\Controllers;

use App\Models\UserType;
use App\Http\Requests\StoreUserTypeRequest;
use App\Http\Requests\UpdateUserTypeRequest;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = UserType::all();
        return \App\Helper\ResponseHelper::success("User roles retrieved successfully", \App\Http\Resources\UserTypeResource::collection($types));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function show(UserType $userType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserType $userType)
    {
        //
    }
    public function destroy(UserType $userType)
    {
        //
    }
}
