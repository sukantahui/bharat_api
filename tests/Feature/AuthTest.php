<?php

use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    //
});

test('user cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/login', [
        'email'    => 'nonexistent@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'status' => false,
        ]);
});

test('login is rate limited after 6 attempts', function () {
    for ($i = 0; $i < 6; $i++) {
        $this->postJson('/api/login', [
            'email'    => 'throttletest@example.com',
            'password' => 'wrongpassword',
        ]);
    }

    $response = $this->postJson('/api/login', [
        'email'    => 'throttletest@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(429);
});

test('authenticated user can access profile', function () {
    $userType = UserType::firstOrCreate(
        ['user_type_name' => 'Admin'],
        ['user_type_name' => 'Admin']
    );

    $user = User::factory()->make([
        'id'           => 99999,
        'email'        => 'testadmin@example.com',
        'user_type_id' => $userType->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/me');
    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
        ]);
});

test('unauthenticated request to protected route returns 401 json', function () {
    $response = $this->getJson('/api/me');
    $response->assertStatus(401)
        ->assertJson([
            'status'  => false,
            'message' => 'Unauthenticated. Please provide a valid Bearer token.',
        ]);
});

test('non-admin user cannot access user management endpoints', function () {
    $studentType = UserType::firstOrCreate(
        ['user_type_name' => 'Student'],
        ['user_type_name' => 'Student']
    );

    $user = User::factory()->make([
        'id'           => 99998,
        'email'        => 'student@example.com',
        'user_type_id' => $studentType->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/users');
    $response->assertStatus(403);
});

test('user can logout and revoke token', function () {
    $userType = UserType::firstOrCreate(
        ['user_type_name' => 'Admin'],
        ['user_type_name' => 'Admin']
    );

    $user = User::factory()->make([
        'id'           => 99997,
        'email'        => 'logouttest@example.com',
        'user_type_id' => $userType->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/logout');
    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
        ]);
});
