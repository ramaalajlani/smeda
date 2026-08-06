<?php

namespace Database\Seeders;

use App\Models\ExecutiveSignerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExecutiveSignerProfileSeeder extends Seeder
{
    public function run(): void
    {
        $general = User::query()->where('email', 'general@system.com')->first();
        $deputy = User::query()->where('email', 'deputy@system.com')->first();

        ExecutiveSignerProfile::query()->updateOrCreate(
            ['role_key' => ExecutiveSignerProfile::ROLE_GENERAL_DIRECTOR],
            [
                'user_id' => $general?->id,
                'signer_name' => $general?->name ?: 'المدير العام',
                'signer_title' => 'المدير العام',
                'is_active' => true,
            ]
        );

        ExecutiveSignerProfile::query()->updateOrCreate(
            ['role_key' => ExecutiveSignerProfile::ROLE_DEPUTY_GENERAL_DIRECTOR],
            [
                'user_id' => $deputy?->id,
                'signer_name' => $deputy?->name ?: 'نائب المدير العام',
                'signer_title' => 'نائب المدير العام',
                'is_active' => true,
            ]
        );
    }
}
