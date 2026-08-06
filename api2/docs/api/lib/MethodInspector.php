<?php

declare(strict_types=1);

namespace ApiDocs;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

final class MethodInspector
{
    public static function analyze(string $controllerClass, string $method, string $projectRoot): array
    {
        $result = [
            'form_request' => null,
            'validation_rules' => [],
            'authorizations' => [],
            'query_params' => [],
            'uses_resource' => null,
            'status_codes' => [],
            'ownership_checks' => [],
            'has_file_upload' => false,
            'notes' => [],
        ];

        if (!class_exists($controllerClass)) {
            $result['notes'][] = 'Controller class غير موجود في autoload.';

            return $result;
        }

        $ref = new ReflectionClass($controllerClass);
        if (!$ref->hasMethod($method)) {
            $result['notes'][] = "Method {$method} غير موجود في {$controllerClass}.";

            return $result;
        }

        $rm = $ref->getMethod($method);
        $result = array_merge($result, self::inspectParameters($rm));

        $file = $ref->getFileName();
        if ($file && is_file($file)) {
            $body = self::extractMethodBody((string) file_get_contents($file), $method);
            if ($body) {
                $result['validation_rules'] = array_merge(
                    $result['validation_rules'],
                    self::extractInlineValidation($body)
                );
                $result['authorizations'] = self::extractAuthorizations($body);
                $result['query_params'] = self::extractQueryParams($body);
                $result['uses_resource'] = self::extractResourceUsage($body);
                $result['status_codes'] = self::extractStatusCodes($body);
                $result['ownership_checks'] = self::extractOwnershipChecks($body);
                $result['has_file_upload'] = self::hasFileUpload(
                    $result['validation_rules'],
                    $result['form_request'],
                    $body
                );
            }
        }

        if ($result['form_request']) {
            $rules = self::extractFormRequestRules($result['form_request'], $projectRoot);
            if ($rules !== []) {
                $result['validation_rules'] = array_merge($result['validation_rules'], $rules);
            }
        }

        return $result;
    }

    private static function inspectParameters(ReflectionMethod $rm): array
    {
        $formRequest = null;
        foreach ($rm->getParameters() as $param) {
            $type = $param->getType();
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }
            $class = $type->getName();
            if (is_subclass_of($class, FormRequest::class)) {
                $formRequest = $class;
            }
        }

        return ['form_request' => $formRequest];
    }

    public static function extractMethodBody(string $source, string $method): ?string
    {
        $pattern = '/function\s+' . preg_quote($method, '/') . '\s*\([^)]*\)(?:\s*:\s*[^{]+)?\s*\{/';
        if (!preg_match($pattern, $source, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $start = (int) $m[0][1] + strlen($m[0][0]);
        $len = strlen($source);
        $depth = 1;
        $i = $start;
        while ($i < $len && $depth > 0) {
            $ch = $source[$i];
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
            }
            $i++;
        }

        return substr($source, $start, $i - $start - 1);
    }

    /** @return array<string, array<int, string>> */
    public static function extractInlineValidation(string $body): array
    {
        $rules = [];
        if (preg_match_all('/\$request->validate\(\s*(\[[\s\S]*?\])\s*(?:,\s*\[[\s\S]*?\])?\s*\)/', $body, $matches)) {
            foreach ($matches[1] as $arraySrc) {
                $parsed = self::parseRulesArray($arraySrc);
                $rules = array_merge($rules, $parsed);
            }
        }
        if (preg_match_all('/\$validated\s*=\s*\$request->validate\(\s*(\[[\s\S]*?\])\s*\)/', $body, $matches)) {
            foreach ($matches[1] as $arraySrc) {
                $parsed = self::parseRulesArray($arraySrc);
                $rules = array_merge($rules, $parsed);
            }
        }

        return $rules;
    }

    /** @return array<string, array<int, string>> */
    public static function parseRulesArray(string $arraySrc): array
    {
        $rules = [];
        if (preg_match_all("/'([^']+)'\s*=>\s*(\[[^\]]*\]|'[^']*'|\"[^\"]*\")/s", $arraySrc, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $field = $match[1];
                $val = trim($match[2]);
                if (str_starts_with($val, '[')) {
                    preg_match_all("/'([^']+)'/", $val, $inner);
                    $rules[$field] = $inner[1] ?? [trim($val, "[]'\" ")];
                } else {
                    $rules[$field] = [trim($val, "'\"")];
                }
            }
        }

        return $rules;
    }

    /** @return list<string> */
    public static function extractAuthorizations(string $body): array
    {
        $out = [];
        if (preg_match_all("/\\\$this->authorize\(\s*'([^']+)'(?:\s*,\s*([^)]+))?\s*\)/", $body, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $policy = trim($match[1]);
                $model = isset($match[2]) ? trim($match[2]) : '';
                $out[] = $model !== '' ? "{$policy} @ {$model}" : $policy;
            }
        }
        if (preg_match_all('/Gate::authorize\(\s*[\'"]([^\'"]+)[\'"]/', $body, $m)) {
            foreach ($m[1] as $g) {
                $out[] = 'Gate:' . $g;
            }
        }

        return array_values(array_unique($out));
    }

    /** @return list<array<string, string>> */
    public static function extractQueryParams(string $body): array
    {
        $params = [];
        $patterns = [
            '/\$request->integer\(\s*[\'"]([^\'"]+)[\'"]/' => 'integer',
            '/\$request->filled\(\s*[\'"]([^\'"]+)[\'"]/' => 'filled filter',
            '/\$request->string\(\s*[\'"]([^\'"]+)[\'"]/' => 'string',
            '/\$request->boolean\(\s*[\'"]([^\'"]+)[\'"]/' => 'boolean',
            '/\$request->input\(\s*[\'"]([^\'"]+)[\'"]/' => 'mixed',
            '/\$request->only\(\s*\[([^\]]+)\]/' => 'only',
        ];
        foreach ($patterns as $regex => $type) {
            if (preg_match_all($regex, $body, $m)) {
                foreach ($m[1] as $raw) {
                    if ($type === 'only') {
                        preg_match_all("/'([^']+)'/", $raw, $fields);
                        foreach ($fields[1] ?? [] as $f) {
                            $params[$f] = ['name' => $f, 'type' => 'mixed', 'source' => 'request->only'];
                        }
                    } else {
                        $params[$raw] = ['name' => $raw, 'type' => $type, 'source' => 'controller'];
                    }
                }
            }
        }
        if (preg_match('/per_page.*?(\d+).*?(\d+)/', $body, $pm)) {
            $params['per_page'] = [
                'name' => 'per_page',
                'type' => 'integer',
                'default' => $pm[1] ?? '20',
                'max' => $pm[2] ?? '100',
                'source' => 'pagination',
            ];
        } elseif (str_contains($body, 'per_page')) {
            $params['per_page'] = [
                'name' => 'per_page',
                'type' => 'integer',
                'source' => 'pagination',
            ];
        }
        if (preg_match('/\$request->filled\(\s*[\'"]q[\'"]/', $body)) {
            $params['q'] = ['name' => 'q', 'type' => 'string', 'min_length' => '2', 'source' => 'search'];
        }

        return array_values($params);
    }

    public static function extractResourceUsage(string $body): ?string
    {
        if (preg_match('/new\s+([A-Za-z0-9\\\\]+Resource)\s*\(/', $body, $m)) {
            return $m[1];
        }
        if (preg_match('/([A-Za-z0-9\\\\]+Resource)::collection/', $body, $m)) {
            return $m[1] . '::collection';
        }

        return null;
    }

    /** @return list<int> */
    public static function extractStatusCodes(string $body): array
    {
        $codes = [];
        if (preg_match_all('/,\s*(\d{3})\s*\)/', $body, $m)) {
            foreach ($m[1] as $c) {
                $codes[] = (int) $c;
            }
        }
        if (preg_match_all('/response\(\)->json\([^,]+,\s*(\d{3})\)/', $body, $m)) {
            foreach ($m[1] as $c) {
                $codes[] = (int) $c;
            }
        }

        return array_values(array_unique($codes));
    }

    /** @return list<string> */
    public static function extractOwnershipChecks(string $body): array
    {
        $checks = [];
        if (preg_match("/where\s*\(\s*'user_id'\s*,\s*\\\$request->user\(\)->id\s*\)/", $body)) {
            $checks[] = 'user_id = current user';
        }
        if (preg_match("/where\s*\(\s*'user_id'\s*,\s*Auth::id\(\)\s*\)/", $body)) {
            $checks[] = 'user_id = Auth::id()';
        }
        if (preg_match('/abort_unless\s*\(/', $body)) {
            $checks[] = 'abort_unless() ownership/role gate';
        }
        if (preg_match('/\$this->authorize\s*\(/', $body)) {
            $checks[] = 'Policy authorize()';
        }
        if (preg_match('/hasPermissionTo\s*\(/', $body)) {
            $checks[] = 'hasPermissionTo() inline';
        }
        if (preg_match('/NeedDataScope::scope/', $body)) {
            $checks[] = 'NeedDataScope geographic filter';
        }
        if (preg_match('/BranchDataScope::/', $body)) {
            $checks[] = 'BranchDataScope filter';
        }
        if (preg_match('/DashboardAccess::/', $body)) {
            $checks[] = 'DashboardAccess::assert';
        }
        if (preg_match('/canManageOwnSignature/', $body)) {
            $checks[] = 'canManageOwnSignature()';
        }
        if (preg_match('/authorizeSnapshotView/', $body)) {
            $checks[] = 'authorizeSnapshotView()';
        }

        return array_values(array_unique($checks));
    }

    public static function hasFileUpload(array $validationRules, ?string $formRequest, string $body): bool
    {
        foreach ($validationRules as $field => $rules) {
            foreach ((array) $rules as $rule) {
                $r = strtolower((string) $rule);
                if (str_contains($r, 'file') || str_contains($r, 'image') || str_contains($r, 'mimes')) {
                    return true;
                }
            }
        }
        return str_contains($body, 'hasFile(') || str_contains($body, "->file(");
    }

    /** @return array<string, array<int, string>> */
    public static function extractFormRequestRules(string $formRequestClass, string $projectRoot): array
    {
        if (!class_exists($formRequestClass)) {
            return [];
        }
        try {
            $ref = new ReflectionClass($formRequestClass);
            if (!$ref->hasMethod('rules')) {
                return [];
            }
            $base = Request::create('/', 'GET');
            app()->instance('request', $base);
            /** @var FormRequest $instance */
            $instance = $ref->newInstanceWithoutConstructor();
            if (method_exists($instance, 'setContainer')) {
                $instance->setContainer(app());
            }
            if (method_exists($instance, 'setRedirector')) {
                $instance->setRedirector(app('redirect'));
            }
            $rulesMethod = $ref->getMethod('rules');
            $rulesMethod->setAccessible(true);
            $rules = $rulesMethod->invoke($instance);
            if (!is_array($rules)) {
                return [];
            }
            $normalized = [];
            foreach ($rules as $field => $rule) {
                if (is_array($rule)) {
                    $parts = [];
                    foreach ($rule as $r) {
                        $parts[] = is_object($r) ? $r::class : (string) $r;
                    }
                    $normalized[$field] = $parts;
                } else {
                    $normalized[$field] = [(string) $rule];
                }
            }

            return $normalized;
        } catch (\Throwable $e) {
            return ['_form_request_error' => ['تعذر استخراج rules: ' . $e->getMessage()]];
        }
    }
}
