<?php

namespace App\Services\Needs;

use App\Models\Need;
use App\Support\NeedTaxonomy;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * بروكسي لاقتراح تصنيف الاحتياج عبر ميكروسيرفس خارجي.
 */
class NeedAiClassifyService
{
    /**
     * @param  array{title?:string,description?:string,sector?:string,district_name?:string}  $input
     * @return array<string, mixed>
     */
    public function suggest(array $input): array
    {
        $url = trim((string) config('services.ai.classify_url', ''));
        if ($url === '') {
            throw new RuntimeException(
                'ميكروسيرفس التصنيف غير مضبوط. عيّن AI_CLASSIFY_URL في ملف .env.'
            );
        }

        $payload = [
            'title' => (string) ($input['title'] ?? ''),
            'description' => (string) ($input['description'] ?? ''),
            'sector' => (string) ($input['sector'] ?? ''),
            'district_name' => (string) ($input['district_name'] ?? ''),
        ];

        $headers = ['Accept' => 'application/json'];
        $token = trim((string) config('services.ai.classify_token', ''));
        if ($token !== '') {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        $method = strtoupper((string) config('services.ai.classify_method', 'POST'));
        $timeout = (int) config('services.ai.classify_timeout', 20);

        $pending = Http::withHeaders($headers)
            ->timeout($timeout)
            ->acceptJson();

        $response = $method === 'GET'
            ? $pending->get($url, $payload)
            : $pending->asJson()->post($url, $payload);

        if (!$response->successful()) {
            throw new RuntimeException(
                'فشل الاتصال بميكروسيرفس التصنيف (HTTP '.$response->status().').'
            );
        }

        $body = $response->json();
        if (!is_array($body)) {
            throw new RuntimeException('استجابة ميكروسيرفس التصنيف غير صالحة.');
        }

        return $this->normalize($body);
    }

    public function suggestForNeed(Need $need): array
    {
        return $this->suggest([
            'title' => (string) $need->title,
            'description' => (string) ($need->description ?? ''),
            'sector' => (string) ($need->sector ?? ''),
            'district_name' => (string) ($need->district_name ?? ''),
        ]);
    }

    /**
     * يوحّد أشكال الاستجابة الشائعة إلى شكل الواجهة.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function normalize(array $body): array
    {
        $root = $body;
        if (isset($body['data']) && is_array($body['data'])) {
            $root = $body['data'];
        }

        $suggestion = [];
        if (isset($root['suggestion']) && is_array($root['suggestion'])) {
            $suggestion = $root['suggestion'];
        } elseif (isset($root['result']) && is_array($root['result'])) {
            $suggestion = $root['result'];
        } else {
            $suggestion = $root;
        }

        $category = $this->stringOrNull($suggestion['need_category'] ?? $suggestion['category'] ?? null);
        $facility = $this->stringOrNull($suggestion['facility_type'] ?? $suggestion['facility'] ?? null);
        $targeting = $this->stringOrNull($suggestion['targeting_type'] ?? $suggestion['targeting'] ?? null);
        $sectors = $suggestion['sector_codes'] ?? $suggestion['sectors'] ?? [];
        if (!is_array($sectors)) {
            $sectors = $sectors ? [(string) $sectors] : [];
        }
        $sectors = array_values(array_filter(array_map('strval', $sectors)));

        $intervention = $this->stringOrNull(
            $suggestion['proposed_intervention'] ?? $suggestion['intervention'] ?? null
        );
        $rationale = $this->stringOrNull($suggestion['rationale'] ?? $suggestion['reason'] ?? $suggestion['explanation'] ?? null);

        $confidence = $root['confidence'] ?? $suggestion['confidence'] ?? null;
        if ($confidence !== null) {
            $confidence = round(min(1, max(0, (float) $confidence)), 2);
        }

        return [
            'engine' => 'microservice',
            'confidence' => $confidence,
            'suggestion' => [
                'need_category' => $category,
                'need_category_label' => $category
                    ? NeedTaxonomy::label(NeedTaxonomy::TYPE_CATEGORY, $category)
                    : null,
                'facility_type' => $facility,
                'facility_type_label' => $facility
                    ? NeedTaxonomy::label(NeedTaxonomy::TYPE_FACILITY, $facility)
                    : null,
                'targeting_type' => $targeting,
                'targeting_type_label' => $targeting
                    ? NeedTaxonomy::label(NeedTaxonomy::TYPE_TARGETING, $targeting)
                    : null,
                'sector_codes' => $sectors,
                'sector_labels' => array_values(array_filter(array_map(
                    fn ($code) => NeedTaxonomy::sectorLabel($code),
                    $sectors
                ))),
                'proposed_intervention' => $intervention,
                'rationale' => $rationale,
            ],
            'raw' => config('app.debug') ? $body : null,
        ];
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
