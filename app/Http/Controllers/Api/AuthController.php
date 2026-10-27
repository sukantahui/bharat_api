<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Helper\ResponseHelper;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    /**
     * Display a listing of all users (Admin only).
     */
    public function index()
    {
        try {
            $users = User::with(['userType', 'employee.department', 'employee.designation'])->orderBy('id', 'desc')->get();
            return ResponseHelper::success('Users retrieved successfully', UserResource::collection($users));
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * Store a newly created user (Admin provisioned).
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'email'        => $request->email,
                'password'     => Hash::make($request->password),
                'user_type_id' => $request->user_type_id,
                'employee_id'  => $request->employee_id,
            ]);

            if ($user) {
                $user->load(['userType', 'employee.department', 'employee.designation']);
                return ResponseHelper::success(
                    'User created successfully',
                    ['user' => new UserResource($user)],
                    201
                );
            } else {
                return ResponseHelper::error('Failed to register user');
            }
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    public function login(LoginRequest $request)
    {
        // To check if database connection is active
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), null, 503);
        }

        try {
            // Attempt to authenticate the user
            if (!Auth::attempt($request->only('email', 'password'))) {
                return ResponseHelper::error('Invalid credentials', null, 401);
            }

            // Get the authenticated user
            $user = Auth::user();

            // Generate a new token for API authentication (Using Laravel Sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            return ResponseHelper::success('login successfully', [
                'user'  => new UserResource($user),
                'token' => $token,
            ], 200);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), null);
        }
    }

    public function getCurrentUser()
    {
        $user = auth()->user();

        if (!$user) {
            return ResponseHelper::error('token expired', null, 401);
        }

        return ResponseHelper::success('User fetched', new UserResource($user), 200);
    }

    public function getCurrentUser2()
    {
        $user = auth()->user();

        if (!$user) {
            return ResponseHelper::error('token expired', null, 401);
        }

        return ResponseHelper::success('User fetched', $user, 200);
    }

    public function logout()
    {
        $user=auth()->user();
        $user->tokens()->delete();
        return ResponseHelper::success('Logged out successfully',$user, 200);
    }
    public function revoke_all(){
        //revoke all tokens from current user
        $result = auth()->user()->tokens()->delete();
        return ResponseHelper::success('Revoke all',$result, 200);
    }


}
