<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;



class Agreement extends Model

{

    protected $fillable = [

        'title',

        'partner_name',

        'agreement_type',

        'scope_type',

        'governorate_id',

        'branch_id',

        'status',

        'start_date',

        'end_date',

        'amount',

        'notes',

        'created_by',

        'approved_by',

    ];



    protected function casts(): array

    {

        return [

            'start_date' => 'date',

            'end_date' => 'date',

            'amount' => 'decimal:2',

        ];

    }



    public function isNational(): bool

    {

        return $this->scope_type === 'national';

    }



    public function isBranchScoped(): bool

    {

        return $this->scope_type === 'branch';

    }



    public function creator(): BelongsTo

    {

        return $this->belongsTo(User::class, 'created_by');

    }



    public function approver(): BelongsTo

    {

        return $this->belongsTo(User::class, 'approved_by');

    }



    public function branch(): BelongsTo

    {

        return $this->belongsTo(Branch::class);

    }



    public function governorate(): BelongsTo

    {

        return $this->belongsTo(Governorate::class);

    }

}

