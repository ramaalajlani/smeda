<?php

declare(strict_types=1);

namespace ApiDocs;

use ReflectionClass;

/**
 * Enriches validation rules from controller/FormRequest PHP source,
 * resolving Rule::in(...) and string "in:" rules not captured in _endpoints.json.
 */
final class ValidationRuleEnricher
{
    public function __construct(private readonly string $projectRoot) {}

    /**
     * @param array<string, array<int, string>> $rules
     * @return array<string, array<int, string>>
     */
    public function enrich(string $controllerClass, string $method, array $rules): array
    {
        if (!class_exists($controllerClass)) {
            return $rules;
        }

        $ref = new ReflectionClass($controllerClass);
        $file = $ref->getFileName();
        if (!$file || !is_file($file)) {
            return $rules;
        }

        $source = (string) file_get_contents($file);
        $body = MethodInspector::extractMethodBody($source, $method);
        if ($body) {
            $rules = $this->mergeInlineRules($body, $rules);
            $rules = $this->mergeRuleInFromBody($body, $rules);
        }

        $formRequest = $rules['_form_request'] ?? null;
        if (is_string($formRequest) && class_exists($formRequest)) {
            $frRef = new ReflectionClass($formRequest);
            $frFile = $frRef->getFileName();
            if ($frFile && is_file($frFile)) {
                $frSource = (string) file_get_contents($frFile);
                if (preg_match('/function\s+rules\s*\([^)]*\)\s*(?::\s*[^{]+)?\s*\{([\s\S]*?)\n\s*\}/', $frSource, $m)) {
                    $rules = $this->mergeInlineRules($m[1], $rules);
                }
            }
        }

        return $rules;
    }

    /**
     * @param array<string, array<int, string>> $rules
     * @return array<string, array<int, string>>
     */
    private function mergeRuleInFromBody(string $body, array $rules): array
    {
        if (!preg_match_all(
            "/['\"]([^'\"]+)['\"]\s*=>\s*\[[^\]]*Rule::in\s*\(\s*([A-Za-z0-9_\\\\]+::\w+\(\)|\[[^\]]*\])\s*\)/",
            $body,
            $matches,
            PREG_SET_ORDER
        )) {
            return $rules;
        }

        foreach ($matches as $match) {
            $field = $match[1];
            $values = $this->evaluateInExpression(trim($match[2]));
            if ($values !== null && $values !== []) {
                $rules[$field] = array_values(array_unique(array_merge(
                    $rules[$field] ?? [],
                    ['in:' . implode(',', $values)]
                )));
            }
        }

        return $rules;
    }

    /**
     * @param array<string, array<int, string>> $rules
     * @return array<string, array<int, string>>
     */
    private function mergeInlineRules(string $body, array $rules): array
    {
        if (preg_match_all(
            "/['\"]([^'\"]+)['\"]\s*=>\s*\[((?:[^\[\]]|\[[^\]]*\])*)\]/s",
            $body,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                $field = $match[1];
                $arrayContent = $match[2];
                $parsed = $this->parseFieldRules($field, $arrayContent);
                if ($parsed !== []) {
                    $rules[$field] = array_values(array_unique(array_merge($rules[$field] ?? [], $parsed)));
                }
            }
        }

        return $rules;
    }

    /** @return array<int, string> */
    private function parseFieldRules(string $field, string $arrayContent): array
    {
        $out = [];

        if (preg_match_all("/'([^']+)'/", $arrayContent, $m)) {
            foreach ($m[1] as $token) {
                if ($token === 'required' || str_contains($token, ':') || in_array($token, [
                    'nullable', 'string', 'integer', 'numeric', 'boolean', 'array', 'email', 'date', 'json', 'uuid',
                    'prohibited', 'sometimes', 'filled', 'present', 'confirmed',
                ], true)) {
                    $out[] = $token;
                }
            }
        }

        if (preg_match('/Rule::in\s*\(\s*([^)]+(?:\([^)]*\))?[^)]*)\s*\)/', $arrayContent, $m)) {
            $values = $this->evaluateInExpression(trim($m[1]));
            if ($values !== null && $values !== []) {
                $out[] = 'in:' . implode(',', $values);
            }
        }

        if (preg_match('/Password::min\s*\(\s*(\d+)\s*\)/', $arrayContent, $m)) {
            $out[] = 'password_min:' . $m[1];
        }

        return $out;
    }

    /** @return list<string>|null */
    private function evaluateInExpression(string $expr): ?array
    {
        if (preg_match('/^\[(.*)\]$/s', $expr, $m)) {
            preg_match_all("/'([^']+)'/", $m[1], $vals);

            return $vals[1] !== [] ? array_values($vals[1]) : null;
        }

        if (preg_match('/^array_keys\s*\(\s*([A-Za-z0-9_\\\\]+)::\$(\w+)\s*\)$/s', $expr, $m)) {
            foreach ($this->resolveClassCandidates($m[1]) as $candidate) {
                $keys = $this->arrayKeysFromStaticProperty($candidate, $m[2]);
                if ($keys !== null) {
                    return $keys;
                }
            }
        }

        if (preg_match('/^([A-Za-z0-9_\\\\]+)::(\w+)\(\)$/s', $expr, $m)) {
            $class = $m[1];
            $method = $m[2];
            foreach ($this->resolveClassCandidates($class) as $candidate) {
                if (!class_exists($candidate) || !method_exists($candidate, $method)) {
                    continue;
                }
                $result = $candidate::$method();
                if (!is_array($result)) {
                    continue;
                }

                return array_values(array_map('strval', $result));
            }
        }

        if (preg_match('/^self::([A-Z_]+)$/s', $expr, $m)) {
            return null;
        }

        return null;
    }

    /** @return list<string>|null */
    private function arrayKeysFromStaticProperty(string $class, string $property): ?array
    {
        if (!class_exists($class)) {
            return null;
        }
        $ref = new ReflectionClass($class);
        if (!$ref->hasProperty($property)) {
            return null;
        }
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $value = $prop->getValue();
        if (!is_array($value)) {
            return null;
        }

        return array_values(array_map('strval', array_keys($value)));
    }

    /** @return list<string> */
    private function resolveClassCandidates(string $class): array
    {
        if (str_contains($class, '\\')) {
            return [$class];
        }

        return [
            $class,
            'App\\Support\\' . $class,
            'App\\Models\\' . $class,
            'App\\Http\\Controllers\\Api\\' . $class,
        ];
    }
}
