<?php

declare(strict_types=1);

namespace ApiDocs;

/**
 * Builds Postman/Apidog sample values from Laravel validation rule arrays.
 * Uses only rule strings resolved from code — no guessed enum values.
 */
final class SampleValueGenerator
{
    /** @var list<string> */
    private array $unresolved = [];

    /** @var array<string, string> */
    private array $idVarByTable = [
        'users' => '{{user_id}}',
        'user' => '{{user_id}}',
        'trainers' => '{{trainer_id}}',
        'trainer' => '{{trainer_id}}',
        'trainees' => '{{trainee_id}}',
        'trainee' => '{{trainee_id}}',
        'training_centers' => '{{training_center_id}}',
        'training_center' => '{{training_center_id}}',
        'branches' => '{{branch_id}}',
        'branch' => '{{branch_id}}',
        'governorates' => '{{governorate_id}}',
        'governorate' => '{{governorate_id}}',
        'certificates' => '{{certificate_id}}',
        'certificate' => '{{certificate_id}}',
        'funding_applications' => '{{funding_application_id}}',
        'funding_application' => '{{funding_application_id}}',
        'courses' => '{{course_id}}',
        'course' => '{{course_id}}',
        'programs' => '{{program_id}}',
        'program' => '{{program_id}}',
        'roles' => '{{role_id}}',
        'role' => '{{role_id}}',
        'permissions' => '{{permission_id}}',
        'permission' => '{{permission_id}}',
        'needs' => '{{need_id}}',
        'need' => '{{need_id}}',
        'incubators' => '{{incubator_id}}',
        'incubator' => '{{incubator_id}}',
        'news' => '{{news_id}}',
        'agreements' => '{{agreement_id}}',
        'agreement' => '{{agreement_id}}',
        'consulting_requests' => '{{consulting_request_id}}',
        'consulting_request' => '{{consulting_request_id}}',
        'consulting_contracts' => '{{consulting_contract_id}}',
        'consulting_contract' => '{{consulting_contract_id}}',
        'job_postings' => '{{job_posting_id}}',
        'job_posting' => '{{job_posting_id}}',
        'training_programs' => '{{training_program_id}}',
        'training_program' => '{{training_program_id}}',
        'training_courses' => '{{training_course_id}}',
        'training_course' => '{{training_course_id}}',
        'training_kits' => '{{training_kit_id}}',
        'training_kit' => '{{training_kit_id}}',
        'funding_partners' => '{{funding_partner_id}}',
        'funding_partner' => '{{funding_partner_id}}',
        'financial_records' => '{{financial_record_id}}',
        'financial_record' => '{{financial_record_id}}',
        'success_stories' => '{{success_story_id}}',
        'success_story' => '{{success_story_id}}',
        'registration_requests' => '{{registration_request_id}}',
        'registration_request' => '{{registration_request_id}}',
    ];

    /** @var array<string, string> */
    private const STRING_SAMPLES = [
        'name' => 'Sample Name',
        'title' => 'Sample Title',
        'headline' => 'Professional headline sample',
        'bio' => 'Brief professional biography for API testing.',
        'description' => 'Sample description for API testing.',
        'summary' => 'Sample summary text.',
        'notes' => 'Sample notes for testing.',
        'comment' => 'Sample review comment.',
        'reason' => 'Sample reason for testing.',
        'message' => 'Sample message body.',
        'subject' => 'Sample subject line',
        'body' => 'Sample message body content.',
        'content' => 'Sample content for testing.',
        'address' => 'Damascus, Syria',
        'city' => 'Damascus',
        'phone' => '+963900000000',
        'mobile' => '+963900000000',
        'national_id' => '12345678901',
        'skills' => 'Leadership, Communication, Training',
        'special_interests' => 'Training and entrepreneurship',
        'linkedin_summary' => 'Experienced trainer focused on SME development.',
        'organization_name' => 'Sample Organization',
        'training_field' => 'Business development',
        'device_name' => 'apidog-client',
        'certificate_code' => '{{certificate_code}}',
        'certificate_number' => '{{certificate_code}}',
        'verification_code' => '{{certificate_code}}',
        'reference_number' => 'REF-SAMPLE-001',
        'email' => 'sample.user@example.com',
        'password' => '{{password}}',
        'password_confirmation' => '{{password}}',
        'current_password' => '{{password}}',
        'q' => 'sample',
        'search' => 'sample',
        'slug' => 'sample-slug',
        'code' => 'SAMPLE-CODE-001',
        'locale' => 'ar',
        'language' => 'ar',
        'currency' => 'SYP',
    ];

    /** @return list<string> */
    public function unresolvedFields(): array
    {
        return array_values(array_unique($this->unresolved));
    }

    /**
     * @param array<string, array<int, string>> $rules
     * @return array<string, mixed>
     */
    public function buildBody(array $rules, bool $hasFileUpload = false): array
    {
        if ($rules === []) {
            return [];
        }

        $flat = [];
        $wildcards = [];

        foreach ($rules as $field => $fieldRules) {
            if (str_starts_with((string) $field, '_')) {
                continue;
            }
            if (preg_match('/^(.+)\.\*\.(.+)$/', (string) $field, $m)) {
                $wildcards[$m[1]][$m[2]] = $fieldRules;
                continue;
            }
            if (preg_match('/^(.+)\.\*$/', (string) $field, $m)) {
                $wildcards[$m[1]]['_scalar'] = $fieldRules;
                continue;
            }
            if (str_contains((string) $field, '.')) {
                continue;
            }
            $flat[$field] = $fieldRules;
        }

        $body = [];
        foreach ($flat as $field => $fieldRules) {
            if ($this->isProhibited($fieldRules) || $this->isFileRule($fieldRules) || $this->isFileFieldName($field)) {
                continue;
            }
            $body[$field] = $this->valueForField($field, $fieldRules);
        }

        foreach ($wildcards as $prefix => $subFields) {
            if (isset($subFields['_scalar'])) {
                if (!$this->isFileRule($subFields['_scalar'])) {
                    $item = $this->valueForField($prefix . '_item', $subFields['_scalar']);
                    $body[$prefix] = [$item];
                }
                continue;
            }
            $item = [];
            foreach ($subFields as $subKey => $subRules) {
                if ($this->isFileRule($subRules)) {
                    continue;
                }
                $item[$subKey] = $this->valueForField($subKey, $subRules);
            }
            if ($item !== []) {
                $body[$prefix] = [$item];
            }
        }

        return $body;
    }

    /**
     * @param array<int, string> $rules
     * @return array<int, string>
     */
    private function normalizeRulesList(array $rules): array
    {
        $out = [];
        foreach ($rules as $rule) {
            $ruleStr = (string) $rule;
            if (str_contains($ruleStr, '|')) {
                foreach (explode('|', $ruleStr) as $part) {
                    $part = trim($part);
                    if ($part !== '') {
                        $out[] = $part;
                    }
                }
                continue;
            }
            $out[] = $ruleStr;
        }

        return $out;
    }

    /**
     * @param array<int, string> $rules
     */
    public function valueForField(string $field, array $rules): mixed
    {
        $rules = $this->normalizeRulesList($rules);
        $normalized = array_map(static fn ($r) => strtolower((string) $r), $rules);

        foreach ($rules as $rule) {
            $ruleStr = (string) $rule;
            if (preg_match('/^in:(.+)$/i', $ruleStr, $m)) {
                $options = array_values(array_filter(array_map('trim', explode(',', $m[1]))));

                return $options[0] ?? $this->markUnresolved($field, 'in rule empty');
            }
        }

        if ($this->containsRuleObject($rules, 'Illuminate\\Validation\\Rules\\Enum')) {
            return $this->markUnresolved($field, 'Rule Enum object — resolve manually');
        }

        foreach ($rules as $rule) {
            if (preg_match('/^exists:([^,]+)/i', (string) $rule, $m)) {
                $table = strtolower(trim($m[1]));

                return $this->idVarByTable[$table] ?? '{{' . $this->singular($table) . '_id}}';
            }
        }

        if (in_array('boolean', $normalized, true) || preg_match('/^(is_|has_|requires_|supports_)/', $field)) {
            return false;
        }

        if (in_array('array', $normalized, true)) {
            $min = $this->ruleInt($normalized, 'min') ?? 0;
            if ($min > 0) {
                return ['sample-item'];
            }

            return [];
        }

        if (in_array('integer', $normalized, true) || in_array('numeric', $normalized, true)) {
            $min = $this->ruleInt($normalized, 'min');
            if ($min !== null && $min > 0) {
                return $min;
            }
            if (preg_match('/_(years|months|count|size|capacity|seats|employees|hours|minutes|order|score|amount)$/', $field)) {
                return max($min ?? 1, 1);
            }
            if (preg_match('/_(id|rate|margin|priority|sort_order)$/', $field)) {
                return $min ?? 1;
            }

            return $min ?? 0;
        }

        if (in_array('email', $normalized, true)) {
            return self::STRING_SAMPLES['email'];
        }

        if ($this->containsRuleObject($rules, 'Illuminate\\Validation\\Rules\\Password')) {
            return 'SamplePass123!';
        }

        foreach ($rules as $rule) {
            if (preg_match('/^password_min:(\d+)$/', (string) $rule, $m)) {
                $len = max(8, (int) $m[1]);

                return str_repeat('A', max(1, $len - 3)) . '1a!';
            }
        }

        if (in_array('date', $normalized, true) || in_array('date_format:Y-m-d', $normalized, true)) {
            return '2026-01-15';
        }

        if (in_array('url', $normalized, true)) {
            return 'https://example.com';
        }

        if (in_array('json', $normalized, true)) {
            return [];
        }

        if (in_array('uuid', $normalized, true)) {
            return '{{uuid}}';
        }

        $lower = strtolower($field);
        if (isset(self::STRING_SAMPLES[$lower])) {
            return self::STRING_SAMPLES[$lower];
        }

        if (preg_match('/_id$/', $lower) && !in_array($lower, ['national_id', 'guardian_national_id'], true)) {
            $base = preg_replace('/_id$/', '', $lower) ?? $lower;

            return '{{' . $base . '_id}}';
        }

        if (in_array('string', $normalized, true) || in_array('nullable', $normalized, true) || in_array('sometimes', $normalized, true)) {
            return 'Sample ' . str_replace('_', ' ', $field);
        }

        if (preg_match('/^required$/', implode('|', $normalized))) {
            return 'Sample ' . str_replace('_', ' ', $field);
        }

        return $this->markUnresolved($field, 'unknown rule set: ' . implode('|', $rules));
    }

    public function pathParamVariable(string $paramName, string $uri): string
    {
        $param = strtolower($paramName);
        if ($param === 'id' || $param === 'uuid') {
            $seg = $this->resourceBeforeParam($uri, $paramName);
            if ($seg !== null) {
                return '{{' . $this->singular(str_replace('-', '_', $seg)) . '_id}}';
            }

            return '{{resource_id}}';
        }

        if (str_ends_with($param, 'id')) {
            return '{{' . str_replace('-', '_', $param) . '}}';
        }

        $camel = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $paramName) ?? $paramName);

        return '{{' . str_replace('-', '_', $camel) . '}}';
    }

    /** @return list<string> */
    public function collectionVariables(): array
    {
        $vars = array_values(array_unique(array_merge(
            array_values($this->idVarByTable),
            ['{{uuid}}', '{{certificate_code}}', '{{resource_id}}', '{{password}}']
        )));
        sort($vars);

        return array_map(static fn ($v) => trim($v, '{}'), $vars);
    }

    private function resourceBeforeParam(string $uri, string $paramName): ?string
    {
        $parts = array_values(array_filter(explode('/', trim($uri, '/'))));
        foreach ($parts as $i => $p) {
            $clean = trim($p, '{}');
            if (strcasecmp($clean, $paramName) !== 0) {
                continue;
            }
            for ($j = $i - 1; $j >= 0; $j--) {
                if (!in_array($parts[$j], ['api', 'admin', 'public'], true)) {
                    return $parts[$j];
                }
            }
        }

        return $this->resourceFromUri($uri);
    }

    private function resourceFromUri(string $uri): ?string
    {
        $parts = array_values(array_filter(explode('/', trim($uri, '/'))));
        for ($i = count($parts) - 1; $i >= 0; $i--) {
            $p = $parts[$i];
            if (!preg_match('/^\{[^}]+\}$/', $p) && !in_array($p, ['api', 'admin', 'public'], true)) {
                return $p;
            }
        }

        return null;
    }

    private function singular(string $table): string
    {
        if (str_ends_with($table, 'ies')) {
            return substr($table, 0, -3) . 'y';
        }
        if (str_ends_with($table, 's')) {
            return substr($table, 0, -1);
        }

        return $table;
    }

    /** @param array<int, string> $rules */
    private function isProhibited(array $rules): bool
    {
        foreach ($rules as $rule) {
            if (strtolower((string) $rule) === 'prohibited') {
                return true;
            }
        }

        return false;
    }

    /** @param array<int, string> $rules */
    private function isFileRule(array $rules): bool
    {
        foreach ($rules as $rule) {
            $r = strtolower((string) $rule);
            if (str_contains($r, 'file') || str_contains($r, 'image') || str_contains($r, 'mimes')) {
                return true;
            }
        }

        return false;
    }

    private function isFileFieldName(string $field): bool
    {
        $f = strtolower($field);

        return in_array($f, [
            'file', 'files', 'image', 'images', 'photo', 'avatar', 'logo', 'attachment', 'attachments',
            'cv', 'resume', 'signature', 'document', 'documents', 'license_image', 'cover_image',
        ], true) || str_contains($f, '_file') || str_contains($f, '_image');
    }

    /** @param array<int, string> $rules */
    private function containsRuleObject(array $rules, string $classFragment): bool
    {
        foreach ($rules as $rule) {
            if (str_contains((string) $rule, $classFragment)) {
                return true;
            }
        }

        return false;
    }

    /** @param array<int, string> $rules */
    private function ruleInt(array $rules, string $name): ?int
    {
        foreach ($rules as $rule) {
            if (preg_match('/^' . preg_quote($name, '/') . ':(\d+)/i', (string) $rule, $m)) {
                return (int) $m[1];
            }
        }

        return null;
    }

    private function markUnresolved(string $field, string $reason): string
    {
        $this->unresolved[] = "{$field}: {$reason}";

        return '';
    }
}
