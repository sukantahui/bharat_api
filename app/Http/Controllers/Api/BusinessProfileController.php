<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBusinessProfileRequest;
use App\Http\Resources\BusinessProfileResource;
use App\Models\BusinessProfile;

class BusinessProfileController extends Controller
{
    public function show()
    {
        $profile = BusinessProfile::first();
        if (!$profile) {
            return ResponseHelper::error('Business profile not configured', null, 404);
        }

        return ResponseHelper::success('Business profile retrieved successfully', new BusinessProfileResource($profile));
    }

    public function update(UpdateBusinessProfileRequest $request)
    {
        $profile = BusinessProfile::firstOrCreate(['id' => 1]);
        $profile->update($request->validated());

        return ResponseHelper::success('Business profile updated successfully', new BusinessProfileResource($profile));
    }
}
