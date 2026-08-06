<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_centers', function (Blueprint $table) {
            $table->string('governorate', 100)->nullable()->after('code');
            $table->string('district', 100)->nullable()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->enum('location_visibility', ['public', 'internal', 'private'])
                ->default('public')
                ->after('longitude');
            $table->string('license_number')->nullable()->after('location_visibility');
            $table->date('license_issue_date')->nullable()->after('license_number');
            $table->date('license_expiry_date')->nullable()->after('license_issue_date');
            $table->string('license_issued_by')->nullable()->after('license_expiry_date');
            $table->string('license_image_path')->nullable()->after('license_issued_by');
        });

        Schema::table('trainers', function (Blueprint $table) {
            $table->string('governorate', 100)->nullable()->after('email');
            $table->string('city', 100)->nullable()->after('governorate');
            $table->string('district', 100)->nullable()->after('city');
            $table->json('service_areas')->nullable()->after('district');
            $table->enum('location_visibility', ['public', 'internal', 'private'])
                ->default('internal')
                ->after('service_areas');
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->string('governorate', 100)->nullable()->after('email');
            $table->string('district', 100)->nullable()->after('city');
            $table->enum('location_visibility', ['public', 'internal', 'private'])
                ->default('private')
                ->after('district');
        });

        Schema::table('training_courses', function (Blueprint $table) {
            $table->string('venue_name')->nullable()->after('approved_platform');
            $table->string('governorate', 100)->nullable()->after('venue_name');
            $table->string('city', 100)->nullable()->after('governorate');
            $table->string('district', 100)->nullable()->after('city');
            $table->string('address')->nullable()->after('district');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->enum('location_visibility', ['public', 'internal', 'private'])
                ->default('public')
                ->after('longitude');
            $table->string('online_platform', 150)->nullable()->after('location_visibility');
            $table->string('online_url')->nullable()->after('online_platform');
        });
    }

    public function down(): void
    {
        Schema::table('training_courses', function (Blueprint $table) {
            $table->dropColumn([
                'venue_name',
                'governorate',
                'city',
                'district',
                'address',
                'latitude',
                'longitude',
                'location_visibility',
                'online_platform',
                'online_url',
            ]);
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->dropColumn(['governorate', 'district', 'location_visibility']);
        });

        Schema::table('trainers', function (Blueprint $table) {
            $table->dropColumn([
                'governorate',
                'city',
                'district',
                'service_areas',
                'location_visibility',
            ]);
        });

        Schema::table('training_centers', function (Blueprint $table) {
            $table->dropColumn([
                'governorate',
                'district',
                'latitude',
                'longitude',
                'location_visibility',
                'license_number',
                'license_issue_date',
                'license_expiry_date',
                'license_issued_by',
                'license_image_path',
            ]);
        });
    }
};
