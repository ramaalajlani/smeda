<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\HasMany;



class ConsultantOffice extends Model

{

    /** @var list<string> */

    public const ASSIGNABLE_STATUSES = ['approved', 'active'];



    protected $fillable = [

        'name', 'license_number', 'governorate_id', 'branch_id', 'specialization', 'sectors',

        'contact_person', 'phone', 'email', 'address', 'status', 'supervised_by_type',

        'approved_by', 'approved_at', 'created_by', 'updated_by',

    ];



    protected $casts = [

        'sectors' => 'array',

        'approved_at' => 'datetime',

    ];



    public static function assignableStatuses(): array

    {

        return self::ASSIGNABLE_STATUSES;

    }



    public function canReceiveAssignments(): bool

    {

        return in_array($this->status, self::ASSIGNABLE_STATUSES, true);

    }



    public function governorate(): BelongsTo

    {

        return $this->belongsTo(Governorate::class);

    }



    public function branch(): BelongsTo

    {

        return $this->belongsTo(Branch::class);

    }



    public function creator(): BelongsTo

    {

        return $this->belongsTo(User::class, 'created_by');

    }



    public function updater(): BelongsTo

    {

        return $this->belongsTo(User::class, 'updated_by');

    }



    public function approver(): BelongsTo

    {

        return $this->belongsTo(User::class, 'approved_by');

    }



    public function assignments(): HasMany

    {

        return $this->hasMany(ConsultantAssignment::class);

    }



    public function reports(): HasMany

    {

        return $this->hasMany(ConsultantReport::class);

    }

}

