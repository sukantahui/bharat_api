<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Http\Requests\StoreStateRequest;
use App\Http\Requests\UpdateStateRequest;
use App\Traits\HandlesTransactions;
use App\Helper\ResponseHelper;
use App\Http\Resources\StateResource;
use Illuminate\Support\Facades\DB;

class StateController extends Controller
{
    use HandlesTransactions;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $states = State::all();
        return ResponseHelper::success("States retrieved successfully",StateResource::collection($states));
    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStateRequest $request)
    {
        $state = DB::transaction(function () use ($request) {
            $data=$request->validated();
            $state=State::create($data);
            return $state;
        });
        return ResponseHelper::success("State created successfully",$state);
    }

    /**
     * Display the specified resource.
     */
    public function show($stateId)
    {
        $state=State::find($stateId);
        if(!$state){
            return ResponseHelper::error("State not found",404);
        }
        return ResponseHelper::success("State retrieved successfully",new StateResource($state));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(State $state)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStateRequest $request, State $state)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(State $state)
    {
        //
    }
}
