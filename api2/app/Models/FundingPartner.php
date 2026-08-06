<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\HasMany;



class FundingPartner extends Model

{

    /** @var list<string> */

    public const ASSIGNABLE_STATUSES = ['approved', 'active'];



    protected $fillable = [

        'name', 'partner_type', 'license_number', 'contact_person', 'phone', 'email',

        'status', 'supervised_by_type', 'approved_by', 'approved_at', 'created_by', 'updated_by',

    ];



    protected $casts = [

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



    public function assignments(): HasMany

    {

        return $this->hasMany(FundingPartnerAssignment::class);

    }



    public function loans(): HasMany

    {

        return $this->hasMany(FundedLoan::class);

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

}

