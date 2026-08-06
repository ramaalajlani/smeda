<?php

use App\Support\NeedTaxonomy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تطوير تصنيف الاحتياجات:
 * - أعمدة جديدة على needs: facility_type / facility_subtype / targeting_type / need_reason.
 * - جدول مرجعي للقطاعات need_sectors + علاقة many-to-many مع needs.
 * - ترحيل آمن لقيم القطاع النصية القديمة إلى العلاقة الجديدة (دون حذف الأعمدة القديمة
 *   sector / economic_sector / syrsic_* — تبقى كما هي للتوافق مع الاحتياجات القديمة).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('needs', function (Blueprint $table) {
            $table->string('facility_type', 100)->nullable()->after('need_category')->index();
            $table->string('facility_subtype', 100)->nullable()->after('facility_type');
            $table->string('targeting_type', 100)->nullable()->after('facility_subtype')->index();
            $table->text('need_reason')->nullable()->after('description');
        });

        Schema::create('need_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('need_need_sector', function (Blueprint $table) {
            $table->id();
            $table->foreignId('need_id')->constrained('needs')->cascadeOnDelete();
            $table->foreignId('need_sector_id')->constrained('need_sectors')->cascadeOnDelete();
            $table->unique(['need_id', 'need_sector_id']);
        });

        $this->seedReferenceSectors();
        $this->migrateLegacySectorValues();
    }

    public function down(): void
    {
        Schema::dropIfExists('need_need_sector');
        Schema::dropIfExists('need_sectors');

        Schema::table('needs', function (Blueprint $table) {
            $table->dropIndex(['facility_type']);
            $table->dropIndex(['targeting_type']);
            $table->dropColumn(['facility_type', 'facility_subtype', 'targeting_type', 'need_reason']);
        });
    }

    private function seedReferenceSectors(): void
    {
        $order = 0;
        foreach (NeedTaxonomy::SECTORS as $code => $nameAr) {
            DB::table('need_sectors')->insertOrIgnore([
                'code' => $code,
                'name_ar' => $nameAr,
                'sort_order' => $order += 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * يربط الاحتياجات القديمة بالقطاعات الجديدة اعتماداً على حقل sector النصي،
     * دون تعديل أو حذف أي بيانات قديمة.
     */
    private function migrateLegacySectorValues(): void
    {
        $sectorIdsByCode = DB::table('need_sectors')->pluck('id', 'code');

        // خريطة: النص العربي القديم أو التسمية الجديدة => كود القطاع
        $textToCode = NeedTaxonomy::LEGACY_SECTOR_MAP;
        foreach (NeedTaxonomy::SECTORS as $code => $label) {
            $textToCode[$label] = $code;
        }

        DB::table('needs')
            ->whereNotNull('sector')
            ->where('sector', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($needs) use ($textToCode, $sectorIdsByCode) {
                foreach ($needs as $need) {
                    $parts = preg_split('/[،,]/u', (string) $need->sector) ?: [];

                    foreach ($parts as $part) {
                        $code = $textToCode[trim($part)] ?? null;
                        $sectorId = $code ? ($sectorIdsByCode[$code] ?? null) : null;

                        if ($sectorId) {
                            DB::table('need_need_sector')->insertOrIgnore([
                                'need_id' => $need->id,
                                'need_sector_id' => $sectorId,
                            ]);
                        }
                    }
                }
            });
    }
};
