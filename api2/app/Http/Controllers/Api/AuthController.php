<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\ApiErrorResponse;
use App\Support\SelfRegistrationCatalog;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}
    /**
     * Self-registration is intentional: users need an active account and token
     * to submit entity registration requests (center/trainer/trainee approval workflow).
     * Entity links (training_center_id, trainer_id, trainee_id) remain null until admin approval.
     * Client cannot pass role/permissions — see validation rules below.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'account_type' => ['required', Rule::in(SelfRegistrationCatalog::validationKeys())],
            'device_name' => ['nullable', 'string', 'max:255'],
            'role' => ['prohibited'],
            'roles' => ['prohibited'],
            'permissions' => ['prohibited'],
            'entity_type' => ['prohibited'],
            'training_center_id' => ['prohibited'],
            'trainer_id' => ['prohibited'],
            'trainee_id' => ['prohibited'],
            'is_active' => ['prohibited'],
        ]);

        $accountType = SelfRegistrationCatalog::normalizeAccountType($validated['account_type']);
        $mapping = SelfRegistrationCatalog::resolveMapping($accountType);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'entity_type' => $mapping['entity_type'],
                'training_center_id' => null,
                'trainer_id' => null,
                'trainee_id' => null,
                'is_active' => true,
            ]);

            // Ensure self-registration role exists to avoid failing account creation
            // when the target environment has not seeded roles yet.
            $role = Role::findOrCreate($mapping['role'], 'sanctum');
            $user->syncRoles([$role->name]);

            $token = $user->createToken(
                $validated['device_name'] ?? 'front-web'
            )->plainTextToken;

            DB::commit();

            $user->refresh();

            return response()->json([
                'message' => 'تم إنشاء الحساب بنجاح.',
                'token' => $token,
                'token_type' => 'Bearer',
                'redirect_to_form' => $accountType,
                'entity_pending_approval' => true,
                'user' => $this->formatUser($user),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(
                ApiErrorResponse::payload($e, 'حدث خطأ غير متوقع، يرجى المحاولة لاحقاً.', 'auth.register_failed'),
                500
            );
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (!Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => true,
        ])) {
            $this->auditLog->log(
                'login_failed',
                null,
                null,
                null,
                null,
                ['email' => $validated['email']],
                $request,
                'users',
                'محاولة تسجيل دخول فاشلة'
            );

            return response()->json([
                'message' => 'بيانات الدخول غير صحيحة.',
            ], 401);
        }

        $user = $request->user();
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken(
            $validated['device_name'] ?? 'front-web'
        )->plainTextToken;

        $this->auditLog->log(
            'login_success',
            $user,
            $user,
            null,
            null,
            ['email' => $user->email],
            $request,
            'users',
            'تسجيل دخول ناجح'
        );

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUser($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $cacheKey = sprintf(
            'auth:me:v1:u%d:%s',
            $user->id,
            md5($user->updated_at?->timestamp . '|' . $user->getRoleNames()->sort()->implode(','))
        );

        $payload = Cache::remember($cacheKey, 60, fn () => $this->formatUser($user));

        return response()->json([
            'user' => $payload,
        ]);
    }

    public function updateMe(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
        ]);
        $user->update($validated);
        return response()->json(['message' => 'تم تحديث بياناتك.', 'user' => $this->formatUser($user->fresh())]);
    }

    public function changeMyPassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);
        $user->update(['password' => $request->string('password')]);
        // إبطال كل التوكنات الأخرى
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح.']);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $this->auditLog->log(
                'logout',
                $user,
                $user,
                null,
                null,
                null,
                $request,
                'users',
                'تسجيل خروج'
            );

            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }

    private function formatUser(User $user): array
    {
        $user->loadMissing('governorate');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? null,
            'entity_type' => $user->entity_type,
            'training_center_id' => $user->training_center_id,
            'trainer_id' => $user->trainer_id,
            'trainee_id' => $user->trainee_id,
            'governorate_id' => $user->governorate_id,
            'governorate_name' => $user->governorate?->name_ar,
            'branch_id' => $user->branch_id,
            'is_active' => (bool) $user->is_active,
            'last_login_at' => $user->last_login_at?->toIso8601String(),
            'created_at' => $user->created_at?->toIso8601String(),
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ];
    }
}