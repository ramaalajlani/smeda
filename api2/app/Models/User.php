<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * مهم لـ Spatie Permission
     */
    protected string $guard_name = 'sanctum';

    /** @var list<string> */
    public const ACCESS_ADMIN_ROLES = ['admin', 'system_admin', 'super_admin'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'entity_type',
        'parent_user_id',
        'training_center_id',
        'training_supervisor_id',
        'trainer_id',
        'trainee_id',
        'governorate_id',
        'branch_id',
        'consultant_office_id',
        'funding_partner_id',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * =========================
     * Relationships
     * =========================
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_user_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_user_id');
    }

    public function trainingCenter(): BelongsTo
    {
        return $this->belongsTo(TrainingCenter::class);
    }

    public function trainingSupervisor(): BelongsTo
    {
        return $this->belongsTo(TrainingSupervisor::class, 'training_supervisor_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function consultantOffice(): BelongsTo
    {
        return $this->belongsTo(ConsultantOffice::class);
    }

    public function fundingPartner(): BelongsTo
    {
        return $this->belongsTo(FundingPartner::class);
    }

    /**
     * =========================
     * Role Helpers
     * =========================
     */
    public function isGeneralDirector(): bool
    {
        return $this->hasRole(['general_director', 'admin', 'super_admin']);
    }

    public function isBranchManager(): bool
    {
        return $this->hasRole('branch_manager');
    }

    public function isDeputyGeneralDirector(): bool
    {
        return $this->hasRole(['deputy_general_director', 'deputy_director']);
    }
    public function isAdmin(): bool
    {
        return $this->isPlatformAdmin();
    }

    /**
     * مدير المنصة — admin أو super_admin (صلاحيات كاملة، بدون تقييد نطاق).
     */
    public function isPlatformAdmin(): bool
    {
        return \App\Support\AccessControlGuard::isPlatformAdministrator($this);
    }

    public function isSystemAdmin(): bool
    {
        return $this->hasRole(self::ACCESS_ADMIN_ROLES);
    }

    public function isTrainingManager(): bool
    {
        return $this->hasRole('training_manager');
    }

    public function isProjectServicesManager(): bool
    {
        return $this->hasRole('project_services_manager');
    }

    public function isTrainingSupervisor(): bool
    {
        return $this->hasRole('training_supervisor');
    }

    public function isDeputy(): bool
    {
        return $this->hasRole('deputy_director');
    }

    public function isCenterUser(): bool
    {
        return $this->hasRole('center_user');
    }

    public function isTrainerUser(): bool
    {
        return $this->hasRole('trainer_user');
    }

    public function isTraineeUser(): bool
    {
        return $this->hasRole('trainee_user');
    }

    public function isAuditor(): bool
    {
        return $this->hasRole('auditor');
    }

    /**
     * =========================
     * Entity Helpers
     * =========================
     */
    public function hasTrainingCenter(): bool
    {
        return !is_null($this->training_center_id);
    }

    public function hasTrainer(): bool
    {
        return !is_null($this->trainer_id);
    }

    public function hasTrainee(): bool
    {
        return !is_null($this->trainee_id);
    }

    public function hasParent(): bool
    {
        return !is_null($this->parent_user_id);
    }

    public function hasChildrenUsers(): bool
    {
        return $this->children()->exists();
    }

    /**
     * =========================
     * Status Helpers
     * =========================
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isAllowed(): bool
    {
        return $this->isActive();
    }

    /**
     * =========================
     * Permission Helpers
     * =========================
     */
    public function canReviewCenterRegistrationRequests(): bool
    {
        return $this->hasPermissionTo('review_center_registration_requests');
    }

    public function canReviewTrainerRegistrationRequests(): bool
    {
        return $this->hasPermissionTo('review_trainer_registration_requests');
    }

    public function canReviewTraineeRegistrationRequests(): bool
    {
        return $this->hasPermissionTo('review_trainee_registration_requests');
    }

    public function canCreateCourseRegistrationRequests(): bool
    {
        return $this->hasPermissionTo('create_course_registration_requests');
    }

    public function canConfirmCourseRegistrationRequests(): bool
    {
        return $this->hasPermissionTo('confirm_course_registration_requests');
    }

    public function canCompleteCourseRegistrationRequests(): bool
    {
        return $this->hasPermissionTo('complete_course_registration_requests');
    }

    /**
     * =========================
     * Access Scope Helpers
     * =========================
     */
    public function belongsToCenter(int|string|null $centerId): bool
    {
        if (blank($centerId)) {
            return false;
        }

        return (int) $this->training_center_id === (int) $centerId;
    }

    public function belongsToTrainer(int|string|null $trainerId): bool
    {
        if (blank($trainerId)) {
            return false;
        }

        return (int) $this->trainer_id === (int) $trainerId;
    }

    public function belongsToTrainee(int|string|null $traineeId): bool
    {
        if (blank($traineeId)) {
            return false;
        }

        return (int) $this->trainee_id === (int) $traineeId;
    }

    public function belongsToParentUser(int|string|null $parentUserId): bool
    {
        if (blank($parentUserId)) {
            return false;
        }

        return (int) $this->parent_user_id === (int) $parentUserId;
    }

    public function isChildOf(int|string|null $userId): bool
    {
        return $this->belongsToParentUser($userId);
    }

    public function canManageUser(User $user): bool
    {
        if ($this->id === $user->id) {
            return true;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return (int) $user->parent_user_id === (int) $this->id;
    }

    public function hasUnrestrictedTrainingAccess(): bool
    {
        return \App\Support\TrainingDataScope::hasUnrestrictedTrainingAccess($this);
    }

    public function hasBroadTrainingReadAccess(): bool
    {
        return \App\Support\TrainingDataScope::hasBroadTrainingReadAccess($this);
    }
}