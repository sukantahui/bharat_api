# 🔐 CNAT API — Authentication & Authorization System Guide
> **Audience:** Beginners & Novices to Laravel / REST APIs  
> **Framework:** Laravel 11.x • **Engine:** Laravel Sanctum (Bearer Tokens) • **Database:** MySQL  
> **Source Files Location:** [`e:/cnat_api`](file:///e:/cnat_api)

---

## 📑 Table of Contents
1. [🌟 The Big Picture: What is API Authentication?](#-1-the-big-picture-what-is-api-authentication)
2. [🏗️ Database Architecture & Table Relationships](#️-2-database-architecture--table-relationships)
3. [🚀 Step-by-Step Authentication Lifecycle](#-3-step-by-step-authentication-lifecycle)
   - [Step 1: System Seeding & Initial Users](#step-1-system-seeding--initial-users)
   - [Step 2: User Registration (Admin Provisioning)](#step-2-user-registration-admin-provisioning)
   - [Step 3: User Login & Token Issuance](#step-3-user-login--token-issuance)
   - [Step 4: Making Authenticated Requests (Sanctum Guard)](#step-4-making-authenticated-requests-sanctum-guard)
   - [Step 5: Role-Based Authorization & Tiered Access](#step-5-role-based-authorization--tiered-access)
   - [Step 6: User Profile Resolution & Transformers](#step-6-user-profile-resolution--transformers)
   - [Step 7: Logout & Token Revocation](#step-7-logout--token-revocation)
4. [📂 Code Map: Functions, Files & Line Numbers](#-4-code-map-functions-files--line-numbers)
5. [🧪 API Testing Playground (cURL & Postman Examples)](#-5-api-testing-playground-curl--postman-examples)
6. [💡 Novice Corner: Common Gotchas & FAQs](#-6-novice-corner-common-gotchas--faqs)

---

## 🌟 1. The Big Picture: What is API Authentication?

### 🏨 The Hotel Keycard Analogy
Imagine you check into a hotel:
1. **Login (`POST /api/login`)**: You show your ID and confirmation at the reception desk.
2. **Token Issuance**: The receptionist validates your identity and hands you a **magnetic keycard** (the `auth_token`).
3. **Protected Access (`auth:sanctum`)**: Whenever you want to enter the elevator or your room, you tap your keycard at the door scanner. You do not need to show your passport and driver's license every single time.
4. **Role Check (`role:Admin,Owner`)**: If you try to tap your guest card at the "Staff Only" or "Penthouse Suite" door, the system beeps red (HTTP `403 Forbidden`).
5. **Logout (`POST /api/logout`)**: When you check out, the receptionist deactivates your keycard in their system so it cannot be used again.

```
┌──────────────┐                  ┌──────────────────────────────────────────────┐
│  Client App  │                  │             CNAT Laravel Backend             │
│ (Postman/Web)│                  │                                              │
└──────┬───────┘                  └──────────────────────┬───────────────────────┘
       │                                                 │
       │ 1. POST /api/login (email, password)            │
       ├────────────────────────────────────────────────>│ Verify password in `users`
       │                                                 │ Generate Sanctum Token in DB
       │ 2. Response: { token: "1|xyz...", user: {...} } │
       │<────────────────────────────────────────────────┤
       │                                                 │
       │ 3. GET /api/me (Header: Authorization: Bearer 1|xyz...)
       ├────────────────────────────────────────────────>│ Sanctum Guard matches token
       │                                                 │ Resolves User Model
       │ 4. Response: { user profile + role }            │
       │<────────────────────────────────────────────────┤
       │                                                 │
       │ 5. POST /api/logout (Header: Bearer 1|xyz...)   │
       ├────────────────────────────────────────────────>│ Deletes token from DB table
       │ 6. Response: "Logged out successfully"          │
       │<────────────────────────────────────────────────┤
```

---

## 🏗️ 2. Database Architecture & Table Relationships

The authentication and authorization system in CNAT API spans across **5 interconnected tables**:

```
 ┌──────────────────────┐               ┌─────────────────────────────────┐
 │     user_types       │               │      personal_access_tokens     │
 ├──────────────────────┤               ├─────────────────────────────────┤
 │ id (PK)              │◄──┐           │ id (PK)                         │
 │ user_type_name       │   │           │ tokenable_type (App\Models\User)│
 │ created_at           │   │           │ tokenable_id   (FK -> users.id) │
 │ updated_at           │   │           │ name           ('auth_token')   │
 └──────────────────────┘   │           │ token          (SHA-256 Hash)   │
                            │           │ abilities      (JSON / NULL)    │
                            │           │ last_used_at   (Timestamp)      │
                            │           │ created_at     (Timestamp)      │
                            │           └────────────────┬────────────────┘
                            │                            │
 ┌──────────────────────┐   │   ┌────────────────────┐   │
 │      employees       │   │   │       users        │   │
 ├──────────────────────┤   │   ├────────────────────┤   │
 │ id (PK)              │◄──┼───┤ id (PK)            │◄──┘
 │ employee_name        │   └───┤ user_type_id (FK)  │
 │ email                │       │ employee_id  (FK)  │
 │ mobile               │   ┌───┤ student_id   (FK)  │
 │ department_id (FK)   │   │   │ email (Unique)     │
 │ designation_id (FK)  │   │   │ password (Hashed)  │
 └──────────────────────┘   │   │ remember_token     │
                            │   │ created_at         │
 ┌──────────────────────┐   │   └────────────────────┘
 │       students       │   │
 ├──────────────────────┤   │
 │ id (PK)              │◄──┘
 │ student_name         │
 │ email                │
 │ phone1 / whatsapp    │
 └──────────────────────┘
```

### Table Details Breakdown

| Table Name | Model File | Purpose | Key Columns |
| :--- | :--- | :--- | :--- |
| **`users`** | [`User.php`](file:///e:/cnat_api/app/Models/User.php) | Holds login credentials and binds to a specific role, employee profile, or student profile. | `id`, `email`, `password` (bcrypt hash), `user_type_id`, `employee_id`, `student_id` |
| **`user_types`** | [`UserType.php`](file:///e:/cnat_api/app/Models/UserType.php) | Defines the distinct roles in the application. | `id`, `user_type_name` (`Admin`, `Developer`, `Owner`, `Manager`, `Teacher`, `Manager Sale`, `Worker`, `Student`) |
| **`personal_access_tokens`** | `Laravel\Sanctum\PersonalAccessToken` | Stores active cryptographic session tokens generated on login. | `id`, `tokenable_type`, `tokenable_id`, `name`, `token` (64-char SHA256), `last_used_at` |
| **`employees`** | [`Employee.php`](file:///e:/cnat_api/app/Models/Employee.php) | Staff profiles containing real name, department, designation, and mobile number. | `id`, `employee_name`, `email`, `mobile`, `department_id`, `designation_id` |
| **`students`** | [`Student.php`](file:///e:/cnat_api/app/Models/Student.php) | Candidate/student profiles containing student name, academic details, and phone. | `id`, `student_name`, `email`, `phone1`, `whatsapp` |

---

## 🚀 3. Step-by-Step Authentication Lifecycle

### Step 1: System Seeding & Initial Users
When the database is freshly migrated and seeded:
1. [`UserTypeSeeder.php`](file:///e:/cnat_api/database/seeders/UserTypeSeeder.php) populates the 8 core role types (`Admin` = 1, `Developer` = 2, `Owner` = 3, `Manager` = 4, `Teacher` = 5, etc.).
2. [`UserSeeder.php`](file:///e:/cnat_api/database/seeders/UserSeeder.php) generates administrative and teacher accounts using `Hash::make('Cnat@1977')`.

---

### Step 2: User Registration (Admin Provisioning)
In CNAT API, user accounts are provisioned by an administrator or authorized manager.

* **Route:** `POST /api/users`  
* **Controller:** [`AuthController::register()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L36-L60)
* **Form Request:** [`RegisterRequest.php`](file:///e:/cnat_api/app/Http/Requests/RegisterRequest.php)

#### How it works:
```php
// 1. Validation runs automatically before hitting the controller
// In RegisterRequest.php:
'email'        => 'required|string|max:200|unique:users,email',
'password'     => 'required|min:8|confirmed',
'user_type_id' => 'required|exists:user_types,id',
'employee_id'  => 'nullable|exists:employees,id',
'student_id'   => 'nullable|exists:students,id',

// 2. Controller securely hashes the plain password and persists the record:
$user = User::create([
    'email'        => $request->email,
    'password'     => Hash::make($request->password), // One-way bcrypt hash!
    'user_type_id' => $request->user_type_id,
    'employee_id'  => $request->employee_id,
    'student_id'   => $request->student_id,
]);
```

---

### Step 3: User Login & Token Issuance
This is the entry point for clients (React web app, mobile app, Insomnia/Postman).

* **Route:** `POST /api/login` (Public route — defined in [`routes/api.php:62-64`](file:///e:/cnat_api/routes/api.php#L62-L64))
* **Controller:** [`AuthController::login()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L62-L87)

#### Execution Steps:
1. **Health Check**: Runs `DB::connection()->getPdo()` to ensure database connectivity. If down, immediately returns HTTP `503 Service Unavailable`.
2. **Credential Check**: Runs `Auth::attempt($request->only('email', 'password'))`.
   - Laravel looks up the record by `email`.
   - Uses `Hash::check($inputPassword, $hashedPasswordFromDB)` to verify the password.
   - If mismatch, returns `401 Unauthorized`: `"Invalid credentials"`.
3. **Token Creation**:
   ```php
   $token = $user->createToken('auth_token')->plainTextToken;
   ```
   - **What happens under the hood?**
     1. Sanctum generates a random 40-character secret string.
     2. Sanctum computes `hash('sha256', $plainTextSecret)`.
     3. Sanctum inserts a row into `personal_access_tokens` storing the hashed string.
     4. Sanctum returns the combined plain text token: `"{token_id}|{random_secret}"` (e.g., `1|a8f9c4b12...`).
4. **Response**: Wraps user data into [`UserResource`](file:///e:/cnat_api/app/Http/Resources/UserResource.php) and returns HTTP `200 OK`.

---

### Step 4: Making Authenticated Requests (Sanctum Guard)
Once a client has received the token string, it attaches it to every subsequent HTTP request in the `Authorization` header:

```http
GET /api/me HTTP/1.1
Host: localhost:8000
Authorization: Bearer 1|a8f9c4b12df38e9...
Accept: application/json
```

#### How Laravel Sanctum handles this:
1. Request hits the `auth:sanctum` middleware specified in [`routes/api.php:71`](file:///e:/cnat_api/routes/api.php#L71).
2. Sanctum extracts `1|a8f9c4b12...` from the `Authorization: Bearer` header.
3. Splits on `|`: ID is `1`, secret is `a8f9c4b12...`.
4. Computes `hash('sha256', $secret)` and queries `personal_access_tokens` where `id = 1` and `token = $hash`.
5. If found and not expired:
   - Updates `last_used_at` timestamp in `personal_access_tokens`.
   - Loads the owner model (`User` with ID from `tokenable_id`).
   - Sets Laravel's authenticated user context: `auth()->user()`.
6. If invalid or missing, returns HTTP `401 Unauthenticated`.

---

### Step 5: Role-Based Authorization & Tiered Access

The system enforces tiered role access using custom middleware:

* **Middleware Registration:** [`bootstrap/app.php:25-27`](file:///e:/cnat_api/bootstrap/app.php#L25-L27) (`'role' => \App\Http\Middleware\CheckRole::class`)
* **Middleware Handler:** [`CheckRole::handle()`](file:///e:/cnat_api/app/Http/Middleware/CheckRole.php)
* **Model Check:** [`User::hasRole()`](file:///e:/cnat_api/app/Models/User.php#L110-L136)

#### Tier Access Matrix:

| Route Group | Middleware Definition | Permitted Roles |
| :--- | :--- | :--- |
| **Self-Service / Personal** | `auth:sanctum` | Any Authenticated User (`Admin`, `Teacher`, `Student`, etc.) |
| **Tier 1 (Admin & System)** | `role:Admin,Developer,Owner` | Full System Access, Users, Departments, Employees |
| **Tier 1.5 (Management)** | `role:Admin,Developer,Owner,Manager` | Student Admissions, Fees Structure, Reports |
| **Tier 2 (Academics & Faculty)** | `role:Admin,Developer,Owner,Manager,Teacher` | Question Bank, Chapters, Topics, Subjects |
| **Tier 3 (Sales & Leads)** | `role:Admin,Developer,Owner,Manager,Teacher,Manager Sale` | Guest Inquiries, Visitor Logs |

#### How `CheckRole` works:
```php
public function handle(Request $request, Closure $next, ...$roles): Response
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
    }

    if (!$user->hasRole($roles)) {
        $required = implode(', ', array_map('trim', $roles));
        return response()->json([
            'status'   => false,
            'message'  => "Forbidden: This action requires the [{$required}] role.",
            'userRole' => $user->role_name,
        ], 403);
    }

    return $next($request);
}
```

---

### Step 6: User Profile Resolution & Transformers
When calling `GET /api/me`, [`AuthController::getCurrentUser()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L89-L99) returns a flattened, unified user object transformed by [`UserResource`](file:///e:/cnat_api/app/Http/Resources/UserResource.php).

#### Profile Unification Logic:
Whether the account belongs to an Employee or a Student, `UserResource` automatically merges and normalizes the attributes:
- **`name`**: `$user->student?->student_name ?? $user->employee?->employee_name ?? $user->email`
- **`role`**: `$user->userType?->user_type_name` (e.g., `"Admin"`, `"Teacher"`, `"Student"`)
- **`mobile`**: `$user->student?->whatsapp ?? $user->employee?->mobile`
- **`department`**: `$user->employee?->department?->department_name ?? ($user->student ? 'Academics' : 'General')`

---

### Step 7: Logout & Token Revocation

* **Single Session Logout (`POST /api/logout`)**:
  - Invokes [`$user->tokens()->delete()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L113-L118).
  - Destroys active tokens for the current user.
* **Global Sign Out Everywhere (`GET /api/revokeAll`)**:
  - Invokes [`$user->tokens()->delete()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L119-L123).
  - Immediately invalidates all tokens across all active browser sessions and mobile devices.

---

## 📂 4. Code Map: Functions, Files & Line Numbers

Click any file link below to open directly in your editor:

### 1. Controllers & Helpers
- [`app/Http/Controllers/Api/AuthController.php`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php)
  - [`index()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L23-L31): Returns all registered users (Admin only).
  - [`register()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L36-L60): Hashes password, links role, saves new user.
  - [`login()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L62-L87): Verifies credentials and generates Sanctum Bearer token.
  - [`getCurrentUser()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L89-L99): Returns authenticated user transformed with `UserResource`.
  - [`logout()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L113-L118): Deletes active access tokens.
  - [`revoke_all()`](file:///e:/cnat_api/app/Http/Controllers/Api/AuthController.php#L119-L123): Revokes all session tokens globally.
- [`app/Helper/ResponseHelper.php`](file:///e:/cnat_api/app/Helper/ResponseHelper.php)
  - [`success()`](file:///e:/cnat_api/app/Helper/ResponseHelper.php#L13-L19): Standardized JSON success structure (`{ status: true, message, data }`).
  - [`error()`](file:///e:/cnat_api/app/Helper/ResponseHelper.php#L20-L26): Standardized JSON error response.

### 2. Models & Relationships
- [`app/Models/User.php`](file:///e:/cnat_api/app/Models/User.php)
  - [`userType()`](file:///e:/cnat_api/app/Models/User.php#L77-L80): `BelongsTo` relationship with `UserType`.
  - [`employee()`](file:///e:/cnat_api/app/Models/User.php#L83-L86): `BelongsTo` relationship with `Employee`.
  - [`student()`](file:///e:/cnat_api/app/Models/User.php#L89-L92): `BelongsTo` relationship with `Student`.
  - [`getRoleNameAttribute()`](file:///e:/cnat_api/app/Models/User.php#L97-L100): Accessor for user's role name.
  - [`hasRole(...)`](file:///e:/cnat_api/app/Models/User.php#L110-L136): Case-insensitive flexible role validation.
  - [`isAdmin()`](file:///e:/cnat_api/app/Models/User.php#L141-L144): Quick check for Admin/Developer/Owner.
- [`app/Models/UserType.php`](file:///e:/cnat_api/app/Models/UserType.php)
  - [`users()`](file:///e:/cnat_api/app/Models/UserType.php#L30-L33): `HasMany` relationship with `User`.

### 3. Middleware & Routing
- [`app/Http/Middleware/CheckRole.php`](file:///e:/cnat_api/app/Http/Middleware/CheckRole.php)
  - [`handle()`](file:///e:/cnat_api/app/Http/Middleware/CheckRole.php#L19-L42): Intercepts requests, validates `$user->hasRole($roles)`, returns 403 on failure.
- [`bootstrap/app.php`](file:///e:/cnat_api/bootstrap/app.php)
  - [Middleware alias registration](file:///e:/cnat_api/bootstrap/app.php#L25-L27) for `'role'`.
- [`routes/api.php`](file:///e:/cnat_api/routes/api.php)
  - [Public Login Route](file:///e:/cnat_api/routes/api.php#L62-L64)
  - [Authenticated Sanctum Group](file:///e:/cnat_api/routes/api.php#L71-L90)
  - [Tier 1 Role Group](file:///e:/cnat_api/routes/api.php#L125-L136)

---

## 🧪 5. API Testing Playground (cURL & Postman Examples)

### 1. Login Request
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "sukantahui",
    "password": "Cnat@1977"
  }'
```

#### Expected 200 OK Response:
```json
{
  "status": true,
  "message": "login successfully",
  "data": {
    "user": {
      "id": 1,
      "userName": "sukantahui",
      "name": "Dr. Sukanta Hui",
      "email": "sukanta@example.com",
      "role": "Admin",
      "mobile": "9876543210",
      "department": "Computer Science",
      "designation": "Director",
      "studentId": null,
      "employeeId": 1
    },
    "token": "1|5jP2A9kQ1zW3r4b8Y7v6u5t4s3r2q1p0o9n8m7l6"
  }
}
```

---

### 2. Profile Request (With Bearer Token)
```bash
curl -X GET http://localhost:8000/api/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|5jP2A9kQ1zW3r4b8Y7v6u5t4s3r2q1p0o9n8m7l6"
```

---

### 3. Unauthorized Test (Forbidden Role)
If a user with role `Teacher` tries to call `GET /api/users`:
```json
{
  "status": false,
  "message": "Forbidden: This action requires the [Admin, Developer, Owner] role.",
  "data": null,
  "userRole": "Teacher"
}
```

---

## 💡 6. Novice Corner: Common Gotchas & FAQs

### Q1: Why do we store passwords as `$2y$12$...` instead of plain text?
> **Answer:** Passwords are never stored in plain text because if a database is compromised, plain text passwords can be read immediately. Laravel uses **Bcrypt**, which is a **one-way cryptographic hash**. It cannot be reversed. When you log in, Laravel hashes your input and checks if both hashes match.

### Q2: Why is the token in the response `1|abcdef...` but in the database it looks like `a8f9c4...`?
> **Answer:** Sanctum stores a **SHA-256 hash** of your token in the database table `personal_access_tokens`. The plain text token is only shown **once** at the moment of login. This ensures that even if an attacker gains read access to the database, they cannot forge active login sessions!

### Q3: What is the difference between Authentication and Authorization?
> - **Authentication (401 Unauthorized)**: *"Who are you?"* (Proving identity via Login and Bearer token).
> - **Authorization (403 Forbidden)**: *"What are you allowed to do?"* (Verifying permissions via role middleware like `role:Admin`).
