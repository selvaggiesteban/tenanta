# Tenanta Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a production-ready multi-tenant SaaS CRM with AI chat capabilities, deployed to Hostinger VPS.

**Architecture:** Monolith Laravel 11 API + Vue 3 SPA. Single database multi-tenancy with `tenant_id` column and global scopes. JWT authentication. Laravel Reverb for WebSockets. Multi-provider AI integration.

**Tech Stack:** Laravel 11, PHP 8.3, MySQL 8, Redis, Vue 3, Vuetify 3, TypeScript, Pinia, Laravel Reverb, JWT Auth, Spatie Permissions

---

# PHASE 1: Foundation (Backend Core)

## Task 1.1: Create Laravel 11 Project

**Files:**
- Create: `tenanta/` (new Laravel project root)
- Modify: `composer.json`

**Step 1: Create Laravel project**

Run:
```bash
cd "/mnt/c/Users/Esteban Selvaggi/Desktop/Nueva carpeta/lanuscomputacion.com/tenanta"
composer create-project laravel/laravel . --prefer-dist
```

Expected: Fresh Laravel 11 installation

**Step 2: Verify installation**

Run:
```bash
php artisan --version
```

Expected: `Laravel Framework 11.x.x`

**Step 3: Configure .env for local development**

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME=Tenanta
APP_URL=http://localhost:8000
DB_DATABASE=tenanta
DB_USERNAME=root
DB_PASSWORD=
```

**Step 4: Commit**

```bash
git init
git add .
git commit -m "chore: initial Laravel 11 setup"
```

---

## Task 1.2: Install Core Dependencies

**Files:**
- Modify: `composer.json`
- Modify: `.env`

**Step 1: Install JWT Auth**

Run:
```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

**Step 2: Install Spatie Permissions**

Run:
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

**Step 3: Install Laravel Reverb**

Run:
```bash
php artisan install:broadcasting
composer require laravel/reverb
php artisan reverb:install
```

**Step 4: Install additional packages**

Run:
```bash
composer require predis/predis
composer require league/csv
composer require barryvdh/laravel-dompdf
```

**Step 5: Configure Redis in .env**

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
BROADCAST_DRIVER=reverb
```

**Step 6: Commit**

```bash
git add .
git commit -m "chore: install core dependencies (jwt, spatie, reverb, redis)"
```

---

## Task 1.3: Create BelongsToTenant Trait

**Files:**
- Create: `app/Traits/BelongsToTenant.php`
- Test: `tests/Unit/Traits/BelongsToTenantTest.php`

**Step 1: Write the failing test**

Create `tests/Unit/Traits/BelongsToTenantTest.php`:

```php
<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tenant;

class BelongsToTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_has_tenant_relationship(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertInstanceOf(Tenant::class, $user->tenant);
        $this->assertEquals($tenant->id, $user->tenant->id);
    }

    public function test_global_scope_filters_by_current_tenant(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        User::factory()->create(['tenant_id' => $tenant1->id]);
        User::factory()->create(['tenant_id' => $tenant2->id]);

        // Simulate setting current tenant
        app()->instance('current_tenant', $tenant1);

        // With global scope, should only see tenant1's user
        $users = User::all();
        $this->assertCount(1, $users);
        $this->assertEquals($tenant1->id, $users->first()->tenant_id);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Traits/BelongsToTenantTest.php`

Expected: FAIL (Tenant and trait don't exist yet)

**Step 3: Create Tenant migration**

Run: `php artisan make:migration create_tenants_table`

Edit migration:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_url', 500)->nullable();
            $table->string('primary_color', 7)->default('#673DE6');
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
```

**Step 4: Create Tenant model**

Create `app/Models/Tenant.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'primary_color',
        'plan_id',
        'trial_ends_at',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
```

**Step 5: Create BelongsToTenant trait**

Create `app/Traits/BelongsToTenant.php`:
```php
<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenant = app('current_tenant')) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenant->id);
            }
        });

        static::creating(function ($model) {
            if ($tenant = app('current_tenant')) {
                $model->tenant_id = $tenant->id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeWithoutTenantScope(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope('tenant');
    }
}
```

**Step 6: Update User model to use trait**

Modify `app/Models/User.php`:
```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'contracted_hours',
        'billable_rate',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'contracted_hours' => 'decimal:2',
            'billable_rate' => 'decimal:2',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'tenant_id' => $this->tenant_id,
            'role' => $this->role,
        ];
    }
}
```

**Step 7: Update users migration**

Run: `php artisan make:migration add_tenant_fields_to_users_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->after('id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['super_admin', 'admin', 'manager', 'member'])->default('member')->after('password');
            $table->decimal('contracted_hours', 4, 2)->default(8.00)->after('role');
            $table->decimal('billable_rate', 10, 2)->default(0.00)->after('contracted_hours');
            $table->string('timezone', 50)->default('America/Argentina/Buenos_Aires')->after('billable_rate');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->softDeletes();

            $table->unique(['email', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'role', 'contracted_hours', 'billable_rate', 'timezone', 'last_login_at', 'deleted_at']);
            $table->dropUnique(['email', 'tenant_id']);
        });
    }
};
```

**Step 8: Create factories**

Create `database/factories/TenantFactory.php`:
```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'primary_color' => '#673DE6',
        ];
    }
}
```

Update `database/factories/UserFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'member',
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
        ]);
    }
}
```

**Step 9: Run migrations and test**

Run:
```bash
php artisan migrate:fresh
php artisan test tests/Unit/Traits/BelongsToTenantTest.php
```

Expected: PASS

**Step 10: Commit**

```bash
git add .
git commit -m "feat: add multi-tenancy with BelongsToTenant trait and global scopes"
```

---

## Task 1.4: Create Tenant Middleware

**Files:**
- Create: `app/Http/Middleware/TenantMiddleware.php`
- Test: `tests/Feature/Middleware/TenantMiddlewareTest.php`
- Modify: `bootstrap/app.php`

**Step 1: Write the failing test**

Create `tests/Feature/Middleware/TenantMiddlewareTest.php`:
```php
<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tenant;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_sets_current_tenant_from_jwt(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $this->assertEquals($tenant->id, app('current_tenant')?->id);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Middleware/TenantMiddlewareTest.php`

Expected: FAIL

**Step 3: Create TenantMiddleware**

Create `app/Http/Middleware/TenantMiddleware.php`:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $tenant = Tenant::find($user->tenant_id);

        if (!$tenant) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }

        app()->instance('current_tenant', $tenant);

        return $next($request);
    }
}
```

**Step 4: Create auth controller for testing**

Create `app/Http/Controllers/Api/Auth/AuthController.php`:
```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'tenant' => [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                ],
            ],
        ]);
    }
}
```

**Step 5: Configure JWT auth guard**

Modify `config/auth.php`:
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

**Step 6: Register middleware and routes**

Modify `bootstrap/app.php`:
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\TenantMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

Create `routes/api.php`:
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::middleware('tenant')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
        });
    });
});
```

**Step 7: Run test**

Run: `php artisan test tests/Feature/Middleware/TenantMiddlewareTest.php`

Expected: PASS

**Step 8: Commit**

```bash
git add .
git commit -m "feat: add TenantMiddleware for JWT-based tenant resolution"
```

---

## Task 1.5: Implement Login and Register

**Files:**
- Modify: `app/Http/Controllers/Api/Auth/AuthController.php`
- Create: `app/Http/Requests/Auth/LoginRequest.php`
- Create: `app/Http/Requests/Auth/RegisterRequest.php`
- Test: `tests/Feature/Auth/LoginTest.php`
- Test: `tests/Feature/Auth/RegisterTest.php`

**Step 1: Write failing login test**

Create `tests/Feature/Auth/LoginTest.php`:
```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tenant;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $tenant = Tenant::factory()->create();
        User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }
}
```

**Step 2: Write failing register test**

Create `tests/Feature/Auth/RegisterTest.php`:
```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Tenant;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_tenant_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'company_name' => 'Acme Corp',
            'name' => 'John Doe',
            'email' => 'john@acme.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => ['id', 'name', 'email'],
                'tenant' => ['id', 'name', 'slug'],
            ]);

        $this->assertDatabaseHas('tenants', ['name' => 'Acme Corp']);
        $this->assertDatabaseHas('users', ['email' => 'john@acme.com', 'role' => 'admin']);
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_name', 'name', 'email', 'password']);
    }
}
```

**Step 3: Run tests to verify they fail**

Run: `php artisan test tests/Feature/Auth/`

Expected: FAIL

**Step 4: Create LoginRequest**

Create `app/Http/Requests/Auth/LoginRequest.php`:
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
```

**Step 5: Create RegisterRequest**

Create `app/Http/Requests/Auth/RegisterRequest.php`:
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
```

**Step 6: Implement AuthController methods**

Update `app/Http/Controllers/Api/Auth/AuthController.php`:
```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth('api')->user();
        $user->update(['last_login_at' => now()]);

        return $this->respondWithToken($token);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name' => $request->company_name,
                'slug' => Str::slug($request->company_name) . '-' . Str::random(6),
                'trial_ends_at' => now()->addDays(14),
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $token = auth('api')->login($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                ],
            ], 201);
        });
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'tenant' => [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                ],
            ],
        ]);
    }

    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ]);
    }
}
```

**Step 7: Update routes**

Update `routes/api.php`:
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        // Public routes
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);

        // Protected routes
        Route::middleware('tenant')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
    });
});
```

**Step 8: Run tests**

Run: `php artisan test tests/Feature/Auth/`

Expected: PASS

**Step 9: Commit**

```bash
git add .
git commit -m "feat: implement login and register with JWT auth"
```

---

## Task 1.6: Create Team Model and CRUD

**Files:**
- Create: `app/Models/Team.php`
- Create: `database/migrations/xxxx_create_teams_table.php`
- Create: `database/migrations/xxxx_create_team_user_table.php`
- Create: `app/Http/Controllers/Api/TeamController.php`
- Test: `tests/Feature/TeamTest.php`

**Step 1: Write failing test**

Create `tests/Feature/TeamTest.php`:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Team;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $tenant = Tenant::factory()->create();
        $this->user = User::factory()->admin()->create(['tenant_id' => $tenant->id]);
        $this->token = auth('api')->login($this->user);
    }

    public function test_can_list_teams(): void
    {
        Team::factory()->count(3)->create(['tenant_id' => $this->user->tenant_id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/teams');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_team(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/teams', [
                'name' => 'Development Team',
                'description' => 'Main dev team',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Development Team');

        $this->assertDatabaseHas('teams', ['name' => 'Development Team']);
    }

    public function test_can_add_member_to_team(): void
    {
        $team = Team::factory()->create(['tenant_id' => $this->user->tenant_id]);
        $member = User::factory()->create(['tenant_id' => $this->user->tenant_id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/v1/teams/{$team->id}/members", [
                'user_id' => $member->id,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($team->members->contains($member));
    }
}
```

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/TeamTest.php`

Expected: FAIL

**Step 3: Create migrations**

Run: `php artisan make:migration create_teams_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
```

Run: `php artisan make:migration create_team_user_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
};
```

**Step 4: Create Team model**

Create `app/Models/Team.php`:
```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
```

**Step 5: Create TeamFactory**

Create `database/factories/TeamFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->words(2, true) . ' Team',
            'description' => fake()->sentence(),
        ];
    }
}
```

**Step 6: Add teams relationship to User**

Update `app/Models/User.php` (add method):
```php
public function teams(): BelongsToMany
{
    return $this->belongsToMany(Team::class)->withTimestamps();
}
```

**Step 7: Create TeamController**

Create `app/Http/Controllers/Api/TeamController.php`:
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        $teams = Team::with('members:id,name,email')->get();

        return response()->json([
            'data' => $teams,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team = Team::create($validated);

        return response()->json([
            'data' => $team,
        ], 201);
    }

    public function show(Team $team): JsonResponse
    {
        return response()->json([
            'data' => $team->load('members:id,name,email'),
        ]);
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team->update($validated);

        return response()->json([
            'data' => $team,
        ]);
    }

    public function destroy(Team $team): JsonResponse
    {
        $team->delete();

        return response()->json(null, 204);
    }

    public function addMember(Request $request, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $team->members()->syncWithoutDetaching([$user->id]);

        return response()->json([
            'data' => $team->load('members:id,name,email'),
        ]);
    }

    public function removeMember(Team $team, User $user): JsonResponse
    {
        $team->members()->detach($user->id);

        return response()->json([
            'data' => $team->load('members:id,name,email'),
        ]);
    }
}
```

**Step 8: Add routes**

Update `routes/api.php`:
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\TeamController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);

        Route::middleware('tenant')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
    });

    Route::middleware('tenant')->group(function () {
        Route::apiResource('teams', TeamController::class);
        Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
        Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);
    });
});
```

**Step 9: Run migrations and test**

Run:
```bash
php artisan migrate
php artisan test tests/Feature/TeamTest.php
```

Expected: PASS

**Step 10: Commit**

```bash
git add .
git commit -m "feat: add Team model with members relationship and CRUD API"
```

---

# PHASE 2: CRM Module

## Task 2.1: Create Client Model and CRUD

**Files:**
- Create: `app/Models/Client.php`
- Create: `database/migrations/xxxx_create_clients_table.php`
- Create: `app/Http/Controllers/Api/CRM/ClientController.php`
- Create: `app/Http/Resources/ClientResource.php`
- Test: `tests/Feature/CRM/ClientTest.php`

*[Continue with similar TDD pattern...]*

---

# Quick Reference: Remaining Tasks

## Phase 2: CRM (Tasks 2.1-2.8)
- 2.1: Client Model and CRUD
- 2.2: Contact Model and CRUD
- 2.3: Lead Model and CRUD
- 2.4: Lead to Client Conversion
- 2.5: Quote Model with Items
- 2.6: Pipeline and Stages
- 2.7: Pipeline Kanban Reorder
- 2.8: CSV Import with Duplicates

## Phase 3: Operations (Tasks 3.1-3.6)
- 3.1: Project Model and CRUD
- 3.2: Task Model with Dependencies
- 3.3: Task Approval Workflow
- 3.4: Timer Server-Side
- 3.5: TimeEntry with Immutability
- 3.6: Overtime Authorization

## Phase 4: Frontend (Tasks 4.1-4.10)
- 4.1: Vue 3 + Vuetify Setup
- 4.2: Auth Store and Routes
- 4.3: Layout Components
- 4.4: CRM Views
- 4.5: Operations Views
- 4.6: Timer Widget
- 4.7: Dashboard Charts
- 4.8: WebSocket Integration
- 4.9: Real-time Updates
- 4.10: PWA Configuration

## Phase 5: Chat AI + Deploy (Tasks 5.1-5.10)
- 5.1: Chat Session Model
- 5.2: AI Provider Interface
- 5.3: Claude Provider
- 5.4: Tool Registry
- 5.5: CRM Tools
- 5.6: External Tools
- 5.7: Chat Streaming
- 5.8: Ticket System
- 5.9: Payment Structure
- 5.10: Hostinger Deployment

---

**Total estimated tasks:** ~40 tasks across 5 phases

Each task follows the same TDD pattern:
1. Write failing test
2. Run test to verify failure
3. Write minimal implementation
4. Run test to verify pass
5. Commit

---

*Plan created: 2026-02-13*
