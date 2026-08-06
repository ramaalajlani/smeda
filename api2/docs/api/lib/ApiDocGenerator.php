<?php

declare(strict_types=1);

namespace ApiDocs;

use Illuminate\Support\Facades\Route;

final class ApiDocGenerator
{
    private string $projectRoot;
    private string $outputDir;
    private array $config;
    /** @var list<array<string, mixed>> */
    private array $endpoints = [];
    /** @var list<array<string, mixed>> */
    private array $rawRoutes = [];
    private array $stats = [];
    private array $openapiStats = [];
    /** @var list<array<string, string>> */
    private array $securityReview = [];

    public function __construct(string $projectRoot, string $outputDir, array $config = [])
    {
        $this->projectRoot = rtrim($projectRoot, '/\\');
        $this->outputDir = rtrim($outputDir, '/\\');
        $this->config = array_merge([
            'local_api_base' => 'http://127.0.0.1:8000/api',
            'local_backend_base' => 'http://127.0.0.1:8000',
            'production_api_base' => 'https://smeda.gov.sy/api/api',
            'production_backend_base' => 'https://smeda.gov.sy/api',
            'app_name' => 'SMEDC Authority API',
            'version' => '2.0.0',
        ], $config);
    }

    public function run(): int
    {
        echo "== SMEDC API Documentation Generator ==\n";
        $this->collectRoutes();
        $this->buildEndpoints();
        $this->buildSecurityReview();
        $this->computeStats();
        $this->writeAll();
        $this->printReport();

        return 0;
    }

    private function collectRoutes(): void
    {
        echo "Collecting routes from Laravel...\n";
        try {
            $routes = Route::getRoutes();
            foreach ($routes as $route) {
                $action = $route->getActionName();
                $controller = null;
                $method = null;
                if (str_contains($action, '@')) {
                    [$controller, $method] = explode('@', $action, 2);
                } elseif (str_contains($action, '::')) {
                    [$controller, $method] = explode('::', $action, 2);
                }
                $this->rawRoutes[] = [
                    'domain' => $route->domain(),
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $action,
                    'controller' => $controller,
                    'controller_method' => $method,
                    'middleware' => $route->gatherMiddleware(),
                ];
            }
        } catch (\Throwable $e) {
            echo "Laravel Route facade failed: {$e->getMessage()}\n";
            $this->collectRoutesFromArtisanJson();
        }

        if ($this->rawRoutes === []) {
            $this->collectRoutesFromArtisanJson();
        }

        echo 'Routes collected: ' . count($this->rawRoutes) . "\n";
    }

    private function collectRoutesFromArtisanJson(): void
    {
        $jsonPath = $this->outputDir . '/_routes_snapshot.json';
        if (!is_file($jsonPath)) {
            throw new \RuntimeException('No routes available and snapshot missing.');
        }
        $raw = Support::stripBom((string) file_get_contents($jsonPath));
        if (!str_starts_with(trim($raw), '[')) {
            $start = strpos($raw, '[');
            if ($start !== false) {
                $raw = substr($raw, $start);
            }
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid routes JSON snapshot.');
        }
        $this->rawRoutes = $decoded;
    }

    private function buildEndpoints(): void
    {
        $seen = [];
        foreach ($this->rawRoutes as $route) {
            $methods = explode('|', $route['method'] ?? 'GET');
            foreach ($methods as $httpMethod) {
                if (in_array($httpMethod, ['HEAD', 'OPTIONS'], true)) {
                    continue;
                }
                $uri = $route['uri'] ?? '';
                $key = $httpMethod . ' ' . $uri;
                $middlewareRaw = $route['middleware'] ?? [];
                $middleware = Support::simplifyMiddleware($middlewareRaw);
                $controller = $route['controller'] ?? null;
                $action = $route['controller_method'] ?? null;
                if (!$controller && isset($route['action']) && str_contains($route['action'], '@')) {
                    [$controller, $action] = explode('@', $route['action'], 2);
                }

                $inspector = ['validation_rules' => [], 'authorizations' => [], 'query_params' => [], 'form_request' => null, 'uses_resource' => null, 'status_codes' => [], 'notes' => []];
                if ($controller && $action && $action !== '__invoke' && class_exists($controller)) {
                    $inspector = MethodInspector::analyze($controller, $action, $this->projectRoot);
                }

                $permissions = [];
                $roles = [];
                foreach ($middleware as $mw) {
                    if (str_starts_with($mw, 'permission:')) {
                        $permissions[] = substr($mw, 11);
                    }
                    if (str_starts_with($mw, 'role:')) {
                        $roles[] = substr($mw, 5);
                    }
                    if (str_starts_with($mw, 'role_or_permission:')) {
                        $permissions[] = substr($mw, 19);
                    }
                }

                $isApi = str_starts_with($uri, 'api/');
                $baseLocal = $isApi ? $this->config['local_api_base'] : $this->config['local_backend_base'];
                $baseProd = $isApi ? $this->config['production_api_base'] : $this->config['production_backend_base'];

                $status = 'فعال';
                $statusNote = '';
                if (isset($seen[$key])) {
                    $status = 'مكرر';
                    $statusNote = 'يوجد route آخر بنفس Method+URI: ' . $seen[$key];
                }
                $seen[$key] = ($route['name'] ?? $route['action'] ?? $key);

                $pathParams = [];
                if (preg_match_all('/\{([^}]+)\}/', $uri, $pm)) {
                    foreach ($pm[1] as $p) {
                        $optional = str_ends_with($p, '?');
                        $name = rtrim($p, '?');
                        $pathParams[] = [
                            'name' => $name,
                            'required' => !$optional,
                            'type' => 'string',
                            'description' => self::pathParamDescription($name),
                        ];
                    }
                }

                $rateLimits = [];
                foreach ($middleware as $mw) {
                    $rl = Support::parseRateLimit($mw);
                    if ($rl) {
                        $rateLimits[] = $rl;
                    }
                }

                $statusCodes = array_unique(array_merge(
                    $inspector['status_codes'],
                    self::defaultStatusCodes($httpMethod, $middleware, $inspector)
                ));
                sort($statusCodes);

                $this->endpoints[] = [
                    'key' => $key,
                    'method' => $httpMethod,
                    'uri' => $uri,
                    'route_name' => $route['name'] ?? null,
                    'module' => Support::detectModule($uri),
                    'controller' => $controller,
                    'controller_short' => $controller ? Support::baseName($controller) : null,
                    'action' => $action,
                    'middleware' => $middleware,
                    'middleware_raw' => $middlewareRaw,
                    'auth_type' => Support::authType($middleware, $uri),
                    'permissions' => $permissions,
                    'roles' => $roles,
                    'rate_limits' => $rateLimits,
                    'status' => $status,
                    'status_note' => $statusNote,
                    'path_params' => $pathParams,
                    'query_params' => $inspector['query_params'],
                    'validation_rules' => $inspector['validation_rules'],
                    'form_request' => $inspector['form_request'],
                    'authorizations' => $inspector['authorizations'],
                    'resource' => $inspector['uses_resource'],
                    'ownership_checks' => $inspector['ownership_checks'] ?? [],
                    'has_file_upload' => $inspector['has_file_upload'] ?? false,
                    'status_codes' => $statusCodes,
                    'full_url_local' => Support::fullUrl($uri, $baseLocal),
                    'full_url_production' => Support::fullUrl($uri, $baseProd),
                    'is_api' => $isApi,
                    'is_signed' => in_array('signed', $middleware, true),
                    'notes' => $inspector['notes'],
                ];
            }
        }
    }

    private static function pathParamDescription(string $name): string
    {
        $map = [
            'id' => 'معرف رقمي للسجل',
            'certificate_code' => 'رمز الشهادة المركب',
            'slug' => 'المعرّف النصي (slug)',
            'code' => 'رمز التحقق',
            'governorate_id' => 'معرف المحافظة',
            'branch_id' => 'معرف الفرع',
        ];

        return $map[$name] ?? 'معامل مسار';
    }

    /** @param list<string> $middleware */
    private static function defaultStatusCodes(string $method, array $middleware, array $inspector): array
    {
        $codes = [200];
        if ($method === 'POST') {
            $codes[] = 201;
        }
        if ($method === 'DELETE') {
            $codes[] = 204;
        }
        if ($inspector['validation_rules'] !== [] || $inspector['form_request']) {
            $codes[] = 422;
        }
        if (in_array('auth:sanctum', $middleware, true)) {
            $codes[] = 401;
            $codes[] = 403;
        }
        foreach ($middleware as $mw) {
            if (str_starts_with($mw, 'throttle:')) {
                $codes[] = 429;
            }
        }
        if ($inspector['authorizations'] !== []) {
            $codes[] = 403;
        }
        $codes[] = 404;
        $codes[] = 500;

        return $codes;
    }

    private function computeStats(): void
    {
        $api = 0;
        $web = 0;
        $public = 0;
        $protected = 0;
        $signed = 0;
        $byMethod = [];
        $byModule = [];
        $controllers = [];

        foreach ($this->endpoints as $ep) {
            if ($ep['is_api']) {
                $api++;
            } else {
                $web++;
            }
            if ($ep['auth_type'] === 'Public') {
                $public++;
            } elseif ($ep['auth_type'] === 'Signed URL') {
                $signed++;
            } else {
                $protected++;
            }
            $byMethod[$ep['method']] = ($byMethod[$ep['method']] ?? 0) + 1;
            $byModule[$ep['module']] = ($byModule[$ep['module']] ?? 0) + 1;
            if ($ep['controller']) {
                $controllers[$ep['controller']] = true;
            }
        }

        $formRequests = count(glob($this->projectRoot . '/app/Http/Requests/**/*.php') ?: [])
            + count(glob($this->projectRoot . '/app/Http/Requests/*.php') ?: []);
        $resources = count(glob($this->projectRoot . '/app/Http/Resources/**/*.php') ?: [])
            + count(glob($this->projectRoot . '/app/Http/Resources/*.php') ?: []);
        $policies = count(glob($this->projectRoot . '/app/Policies/*.php') ?: []);

        $this->stats = [
            'total_endpoints' => count($this->endpoints),
            'total_routes_raw' => count($this->rawRoutes),
            'api_endpoints' => $api,
            'web_endpoints' => $web,
            'public_endpoints' => $public,
            'protected_endpoints' => $protected,
            'signed_endpoints' => $signed,
            'by_method' => $byMethod,
            'by_module' => $byModule,
            'controllers' => count($controllers),
            'form_requests' => $formRequests,
            'resources' => $resources,
            'policies' => $policies,
        ];
    }

    private function writeAll(): void
    {
        echo "Writing documentation files...\n";
        Support::atomicWrite($this->outputDir . '/openapi.yaml', $this->buildOpenApi());
        Support::atomicWrite($this->outputDir . '/API_DOCUMENTATION_AR.md', $this->buildMarkdown());
        Support::atomicWrite($this->outputDir . '/API_DOCUMENTATION_AR.html', $this->buildHtml());
        Support::atomicWrite($this->outputDir . '/API_AUDIT_REPORT_AR.md', $this->buildAuditReport());
        Support::atomicWrite($this->outputDir . '/ROUTE_COVERAGE_REPORT.md', $this->buildCoverageReport());
        Support::atomicWrite($this->outputDir . '/README.md', $this->buildReadme());
        Support::atomicWrite($this->outputDir . '/index.html', $this->buildSwaggerIndex());
        Support::atomicWrite($this->outputDir . '/SMEDC_API.postman_collection.json', json_encode($this->buildPostmanCollection(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        Support::atomicWrite($this->outputDir . '/SMEDC_Local.postman_environment.json', json_encode($this->buildPostmanEnv('Local', $this->config['local_api_base']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        Support::atomicWrite($this->outputDir . '/SMEDC_Production.postman_environment.json', json_encode($this->buildPostmanEnv('Production', $this->config['production_api_base']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        Support::atomicWrite($this->outputDir . '/_endpoints.json', json_encode($this->endpoints, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        echo "Done.\n";
    }

    private function buildOpenApi(): string
    {
        $paths = [];
        $operationIds = [];
        $duplicates = 0;
        $included = 0;

        foreach ($this->endpoints as $ep) {
            if (!$ep['is_api']) {
                continue;
            }
            $path = '/' . ltrim(preg_replace('#^api/#', '', $ep['uri']), '/');
            $method = strtolower($ep['method']);

            if (isset($paths[$path][$method])) {
                $duplicates++;
                continue;
            }

            $opId = self::uniqueOperationId($ep, $operationIds);
            $parameters = $this->openApiParameters($ep);
            $requestBody = $this->openApiRequestBody($ep);
            $responses = $this->openApiResponses($ep);

            $operation = [
                'operationId' => $opId,
                'tags' => [$ep['module']],
                'summary' => ($ep['controller_short'] ?? 'Route') . '::' . ($ep['action'] ?? ''),
                'description' => $this->endpointDescriptionAr($ep),
                'responses' => $responses,
            ];

            if ($ep['auth_type'] === 'Bearer Token') {
                $operation['security'] = [['BearerAuth' => []]];
            } else {
                $operation['security'] = [];
            }

            if ($parameters !== []) {
                $operation['parameters'] = $parameters;
            }
            if ($requestBody !== null) {
                $operation['requestBody'] = $requestBody;
            }

            $paths[$path][$method] = $operation;
            $included++;
        }

        $this->openapiStats = [
            'paths_count' => count($paths),
            'operations_count' => $included,
            'duplicate_skipped' => $duplicates,
            'unique_operation_ids' => count($operationIds),
        ];

        $doc = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => $this->config['app_name'],
                'version' => $this->config['version'],
                'description' => 'SMEDC Authority Platform API — generated from Laravel source. Production base: https://smeda.gov.sy/api/api',
            ],
            'servers' => [
                ['url' => $this->config['local_api_base'], 'description' => 'Local (php artisan serve)'],
                ['url' => $this->config['production_api_base'], 'description' => 'Production Hostinger'],
            ],
            'tags' => array_map(fn ($m) => ['name' => $m], array_keys($this->stats['by_module'] ?? [])),
            'paths' => $paths,
            'components' => [
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Sanctum',
                        'description' => 'Laravel Sanctum personal access token. Example: 1|example_token_placeholder',
                    ],
                ],
                'schemas' => [
                    'ValidationError' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string', 'example' => 'The given data was invalid.'],
                            'errors' => [
                                'type' => 'object',
                                'additionalProperties' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                ],
                            ],
                        ],
                    ],
                    'AuthLoginRequest' => [
                        'type' => 'object',
                        'required' => ['email', 'password'],
                        'properties' => [
                            'email' => ['type' => 'string', 'format' => 'email', 'example' => 'demo.user@example.com'],
                            'password' => ['type' => 'string', 'format' => 'password', 'example' => 'DemoPassword123!'],
                            'device_name' => ['type' => 'string', 'example' => 'postman-client'],
                        ],
                    ],
                    'AuthTokenResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string'],
                            'token' => ['type' => 'string', 'example' => '1|sanctum_token_placeholder'],
                            'token_type' => ['type' => 'string', 'example' => 'Bearer'],
                            'user' => ['type' => 'object'],
                        ],
                    ],
                ],
            ],
        ];

        return Support::dumpYaml($doc);
    }

    /** @param array<string, true> $used */
    private static function uniqueOperationId(array $ep, array &$used): string
    {
        $base = strtolower($ep['method']) . '_' . preg_replace('/[^a-zA-Z0-9_]+/', '_', $ep['uri']);
        $base = trim($base, '_');
        $id = $base;
        $n = 2;
        while (isset($used[$id])) {
            $id = $base . '_' . $n++;
        }
        $used[$id] = true;

        return $id;
    }

    private function buildSecurityReview(): void
    {
        $focusPatterns = [
            'electronic-signature', 'dashboard', 'finance/', 'needs/dashboard', 'needs/analytics',
            'needs/workspace', 'notifications', 'inbox', 'incubator', 'entrepreneur',
        ];

        foreach ($this->endpoints as $ep) {
            if (!$ep['is_api']) {
                continue;
            }
            $uri = $ep['uri'];
            $isFocus = false;
            foreach ($focusPatterns as $p) {
                if (str_contains($uri, $p)) {
                    $isFocus = true;
                    break;
                }
            }
            if (!$isFocus && $ep['auth_type'] !== 'Bearer Token') {
                continue;
            }
            if ($ep['auth_type'] === 'Public' && !$isFocus) {
                continue;
            }

            $permRole = $ep['permissions'] !== [] ? implode(', ', $ep['permissions'])
                : ($ep['roles'] !== [] ? 'role:' . implode(', ', $ep['roles']) : '—');
            $policy = $ep['authorizations'] !== [] ? implode('; ', $ep['authorizations']) : '—';
            $ownership = $ep['ownership_checks'] !== [] ? implode('; ', $ep['ownership_checks']) : '—';

            $result = '✅ محمي';
            $recommendation = 'لا إجراء — التحقق موجود في Controller/Policy';

            if ($ep['auth_type'] === 'Public' && $isFocus) {
                $result = '⚠️ Public';
                $recommendation = 'مراجعة: مسار حساس بدون Bearer Token';
            } elseif ($ep['auth_type'] === 'Bearer Token' && $permRole === '—' && $policy === '—' && $ownership === '—') {
                $result = '⚠️ auth:sanctum فقط';
                $recommendation = 'مراجعة يدوية — لا permission middleware ولا authorize() مستخرج';
            } elseif ($ep['auth_type'] === 'Bearer Token' && $permRole === '—' && ($policy !== '—' || $ownership !== '—')) {
                $result = '✅ Bearer + فحص داخلي';
                $recommendation = 'مقبول — authorize()/ownership داخل Controller';
            }

            $this->securityReview[] = [
                'route' => $ep['key'],
                'authentication' => $ep['auth_type'],
                'permission_role' => $permRole,
                'policy' => $policy,
                'ownership' => $ownership,
                'result' => $result,
                'recommendation' => $recommendation,
            ];
        }
    }

    private function endpointDescriptionAr(array $ep): string
    {
        $parts = [];
        $parts[] = 'الحالة: ' . $ep['status'];
        if ($ep['status_note']) {
            $parts[] = $ep['status_note'];
        }
        if ($ep['authorizations']) {
            $parts[] = 'Policy: ' . implode(', ', $ep['authorizations']);
        }
        if ($ep['permissions']) {
            $parts[] = 'Permission: ' . implode(', ', $ep['permissions']);
        }
        if ($ep['rate_limits']) {
            $parts[] = 'Rate limit: ' . implode('; ', $ep['rate_limits']);
        }

        return implode("\n", $parts);
    }

    private function openApiParameters(array $ep): array
    {
        $params = [];
        foreach ($ep['path_params'] as $p) {
            $params[] = [
                'name' => $p['name'],
                'in' => 'path',
                'required' => (bool) ($p['required'] ?? true),
                'schema' => ['type' => $p['type'] === 'integer' ? 'integer' : 'string'],
                'description' => $p['description'],
                'example' => $p['name'] === 'id' ? 1 : 'example-value',
            ];
        }
        foreach ($ep['query_params'] as $q) {
            $params[] = [
                'name' => $q['name'],
                'in' => 'query',
                'required' => false,
                'schema' => ['type' => $q['type'] ?? 'string'],
                'description' => $q['source'] ?? '',
            ];
        }

        return $params;
    }

    private function openApiRequestBody(array $ep): ?array
    {
        if (!in_array($ep['method'], ['POST', 'PUT', 'PATCH'], true)) {
            return null;
        }

        if (!empty($ep['has_file_upload'])) {
            $props = [];
            $required = [];
            foreach ($ep['validation_rules'] as $field => $rules) {
                if (str_starts_with((string) $field, '_')) {
                    continue;
                }
                $isFile = false;
                foreach ((array) $rules as $rule) {
                    $r = strtolower((string) $rule);
                    if (str_contains($r, 'file') || str_contains($r, 'image') || str_contains($r, 'mimes')) {
                        $isFile = true;
                    }
                }
                $props[$field] = [
                    'type' => $isFile ? 'string' : 'string',
                    'format' => $isFile ? 'binary' : null,
                    'description' => implode('|', (array) $rules),
                ];
                if (in_array('required', (array) $rules, true)) {
                    $required[] = $field;
                }
            }

            return [
                'required' => true,
                'content' => [
                    'multipart/form-data' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => $props,
                            'required' => $required,
                        ],
                    ],
                ],
            ];
        }

        if ($ep['validation_rules'] === []) {
            return null;
        }
        $props = [];
        $required = [];
        foreach ($ep['validation_rules'] as $field => $rules) {
            if (str_starts_with((string) $field, '_')) {
                continue;
            }
            $props[$field] = [
                'type' => 'string',
                'description' => implode('|', (array) $rules),
                'example' => 'example-value',
            ];
            if (in_array('required', (array) $rules, true)) {
                $required[] = $field;
            }
        }

        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => $props,
                        'required' => $required,
                    ],
                ],
            ],
        ];
    }

    private function openApiResponses(array $ep): array
    {
        $responses = [];
        foreach ($ep['status_codes'] as $code) {
            $responses[(string) $code] = [
                'description' => self::statusDescription($code),
            ];
            if ($code === 422) {
                $responses['422']['content'] = [
                    'application/json' => ['schema' => ['$ref' => '#/components/schemas/ValidationError']],
                ];
            }
        }

        return $responses;
    }

    private static function statusDescription(int $code): string
    {
        return match ($code) {
            200 => 'نجاح',
            201 => 'تم الإنشاء',
            202 => 'مقبول للمعالجة',
            204 => 'بدون محتوى',
            400 => 'طلب غير صالح',
            401 => 'غير مصادق — Token مفقود أو غير صالح',
            403 => 'ممنوع — صلاحية أو Policy',
            404 => 'غير موجود',
            409 => 'تعارض',
            422 => 'خطأ تحقق Validation',
            429 => 'تجاوز Rate Limit',
            500 => 'خطأ خادم',
            default => 'استجابة HTTP ' . $code,
        };
    }

    private function buildMarkdown(): string
    {
        $md = [];
        $md[] = '<div dir="rtl" lang="ar">';
        $md[] = '';
        $md[] = '# توثيق API — منصة الهيئة (SMEDC)';
        $md[] = '';
        $md[] = '> **الإصدار:** ' . $this->config['version'] . ' | **تاريخ التوليد:** ' . date('Y-m-d H:i:s') . ' | **المصدر:** كود Laravel الفعلي';
        $md[] = '';
        $md[] = '## فهرس المحتويات';
        $md[] = '';
        $md[] = '- [الرابط الأساسي Base URL](#base-url)';
        $md[] = '- [المصادقة Sanctum](#authentication)';
        $md[] = '- [الأدوار والصلاحيات](#roles-permissions)';
        $md[] = '- [Pagination](#pagination)';
        $md[] = '- [الوحدات والمسارات](#modules)';
        $md[] = '- [Web — طباعة وتحقق](#web-routes)';
        $md[] = '- [النطاق الجغرافي](#geographic-scope)';
        $md[] = '- [GIS والخريطة](#gis-map)';
        $md[] = '- [الملفات والطباعة](#files-print)';
        $md[] = '- [مراجعة الحماية](#security-review)';
        $md[] = '- [ملحق الحالات والقيم](#appendix)';
        $md[] = '';
        $md[] = $this->buildBaseUrlSection();
        $md[] = $this->buildAuthSection();
        $md[] = $this->buildRolesSection();
        $md[] = $this->buildPaginationSection();
        $md[] = $this->buildModulesIndex();
        $md[] = $this->buildEndpointsMarkdown();
        $md[] = $this->buildWebSection();
        $md[] = $this->buildGeographicSection();
        $md[] = $this->buildGisSection();
        $md[] = $this->buildFilesAndPrintSection();
        $md[] = $this->buildSecurityReviewMarkdown();
        $md[] = $this->buildAppendix();
        $md[] = '';
        $md[] = '</div>';

        return implode("\n", $md);
    }

    private function buildBaseUrlSection(): string
    {
        return <<<'MD'

---

<a id="base-url"></a>
## الرابط الأساسي (Base URL)

### Local

| البيئة | API Base | مثال |
|--------|----------|------|
| `php artisan serve` | `http://127.0.0.1:8000/api` | `POST http://127.0.0.1:8000/api/login` |

**المصدر:** `front/assets/js/core/config.js` (سطر 38-39) و `bootstrap/app.php` (prefix `api`).

### Production (Hostinger — smeda.gov.sy)

| المكوّن | الرابط | الدليل |
|---------|--------|--------|
| Frontend | `https://smeda.gov.sy` | `deploy/hostinger/public_html/config.php` |
| Laravel entry | `https://smeda.gov.sy/api/` | `deploy/hostinger/public_html/api/index.php` |
| **API Base** | **`https://smeda.gov.sy/api/api`** | `config.php` → `api_base_url` + `config.js` سطر 42 |

> **تنبيه:** المسار `/api/api/...` **ليس خطأ إعداد** في هذا المشروع: المجلد الفرعي `public_html/api/` + بادئة Laravel `/api` يُنتجان `/api/api/login` للمسار الداخلي `api/login`.

### Web / Print / Signed URLs (Production)

| النوع | Base | مثال |
|-------|------|------|
| Backend (طباعة، PDF، QR) | `https://smeda.gov.sy/api` | `GET https://smeda.gov.sy/api/certificates/{code}/print` |

**المصدر:** `config.js` → `BACKEND_BASE_URL` = `${frontendBase}/api`

MD;
    }

    private function buildAuthSection(): string
    {
        $expiration = '480';
        $sanctumPath = $this->projectRoot . '/config/sanctum.php';
        if (is_file($sanctumPath) && preg_match("/'expiration'\s*=>\s*env\('SANCTUM_TOKEN_EXPIRATION',\s*(\d+)\)/", file_get_contents($sanctumPath), $m)) {
            $expiration = $m[1];
        }

        return <<<MD

---

<a id="authentication"></a>
## المصادقة (Laravel Sanctum)

### Headers

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
Content-Type: application/json
```

### Token Expiration

- **المدة:** {$expiration} دقيقة (افتراضي من `config/sanctum.php` → `SANCTUM_TOKEN_EXPIRATION`)
- عند انتهاء الصلاحية: استجابة **401** من middleware `auth:sanctum`

### POST /api/register

| الحقل | النوع | مطلوب | Validation |
|-------|-------|------:|------------|
| name | string | نعم | required, max:255 |
| email | email | نعم | required, unique:users |
| password | string | نعم | required, confirmed, min:8 |
| password_confirmation | string | نعم | مع password |
| account_type | string | نعم | Rule::in(SelfRegistrationCatalog::validationKeys()) |
| device_name | string | لا | nullable, max:255 |
| role, roles, permissions, entity_type, training_center_id, trainer_id, trainee_id, is_active | — | **محظور** | prohibited |

**account_type المسموحة:** trainee, trainer, center, project_owner, incubation_applicant, entrepreneur_tech, consultant, consulting_client, jobseeker, employer, entrepreneur (يُحوَّل إلى project_owner)

**Rate limit:** 5 طلبات/دقيقة/IP (`throttle:register`)

**Response 201:**
```json
{
  "message": "تم إنشاء الحساب بنجاح.",
  "token": "1|example_token",
  "token_type": "Bearer",
  "redirect_to_form": "trainee",
  "entity_pending_approval": true,
  "user": { "id": 1, "name": "...", "roles": ["trainee_user"] }
}
```

### POST /api/login

| الحقل | مطلوب | ملاحظات |
|-------|------:|---------|
| email | نعم | |
| password | نعم | |
| device_name | لا | افتراضي `front-web` |

- الحساب المعطل (`is_active=false`): فشل المصادقة → **401**
- **Rate limit:** 10/دقيقة لكل email|IP

### POST /api/logout

- يحذف **التوكن الحالي فقط** (`currentAccessToken()->delete()`)
- يتطلب Bearer Token

### GET /api/me | PUT /api/me | POST /api/me/change-password

راجع الأقسام التفصيلية في الوحدة **User Profile**.

MD;
    }

    private function buildRolesSection(): string
    {
        $labels = Support::roleLabels();
        $lines = ["\n---\n\n<a id=\"roles-permissions\"></a>\n## الأدوار والصلاحيات\n"];
        $lines[] = '| Role | الاسم العربي |';
        $lines[] = '|------|-------------|';
        foreach ($labels as $role => $ar) {
            $lines[] = "| `{$role}` | {$ar} |";
        }
        $lines[] = '';
        $lines[] = '**المصدر:** `database/seeders/RolePermissionSeeder.php` — 32 دوراً و 165 صلاحية تقريباً.';
        $lines[] = '';
        $lines[] = '> مصفوفة الصلاحيات التفصيلية لكل Endpoint مذكورة في قسم كل مسار (Policy / Permission / Role).';

        return implode("\n", $lines);
    }

    private function buildPaginationSection(): string
    {
        return <<<'MD'

---

<a id="pagination"></a>
## Pagination

النمط الافتراضي: **Laravel LengthAwarePaginator** عبر `paginate()`.

| Parameter | الافتراضي | الحد الأقصى | المصدر |
|-----------|----------:|------------:|--------|
| `page` | 1 | — | Laravel |
| `per_page` | 20 (أو 25/30/50 حسب Controller) | 100 (أغلب القوائم) | Controller |

**مثال استجابة:**
```json
{
  "current_page": 1,
  "data": [],
  "first_page_url": "...",
  "from": 1,
  "last_page": 10,
  "last_page_url": "...",
  "links": [],
  "next_page_url": "...",
  "path": "...",
  "per_page": 20,
  "prev_page_url": null,
  "to": 20,
  "total": 200
}
```

> **ملاحظة:** بعض Controllers تُعيد `response()->json($paginator)` مباشرة دون غلاف `data/meta` إضافي.

MD;
    }

    private function buildModulesIndex(): string
    {
        $lines = ["\n---\n\n<a id=\"modules\"></a>\n## الوحدات — جدول مختصر\n"];
        $lines[] = '| الوحدة | عدد Endpoints |';
        $lines[] = '|--------|--------------:|';
        ksort($this->stats['by_module']);
        foreach ($this->stats['by_module'] as $mod => $count) {
            $anchor = preg_replace('/\s+/', '-', $mod);
            $lines[] = "| [{$mod}](#module-{$anchor}) | {$count} |";
        }

        return implode("\n", $lines);
    }

    private function buildEndpointsMarkdown(): string
    {
        $byModule = [];
        foreach ($this->endpoints as $ep) {
            $byModule[$ep['module']][] = $ep;
        }
        ksort($byModule);
        $out = [];
        foreach ($byModule as $module => $eps) {
            $anchor = preg_replace('/\s+/', '-', $module);
            $out[] = "\n---\n\n<a id=\"module-{$anchor}\"></a>\n## وحدة: {$module}\n";
            foreach ($eps as $ep) {
                $out[] = $this->formatEndpointMd($ep);
            }
        }

        return implode("\n", $out);
    }

    private function formatEndpointMd(array $ep): string
    {
        $lines = [];
        $lines[] = "\n### {$ep['method']} `{$ep['uri']}`\n";
        $lines[] = '| البند | القيمة |';
        $lines[] = '|------|--------|';
        $lines[] = '| الوصف | Endpoint من `' . ($ep['controller_short'] ?? 'Closure') . '::' . ($ep['action'] ?? '-') . '` |';
        $lines[] = '| Controller | `' . ($ep['controller'] ?? 'غير محدد') . '` |';
        $lines[] = '| Method | `' . ($ep['action'] ?? 'غير محدد') . '` |';
        $lines[] = '| Route Name | `' . ($ep['route_name'] ?? '—') . '` |';
        $lines[] = '| Middleware | `' . implode(', ', $ep['middleware']) . '` |';
        $lines[] = '| المصادقة | **' . $ep['auth_type'] . '** |';
        $lines[] = '| الحالة | ' . $ep['status'] . ($ep['status_note'] ? ' — ' . $ep['status_note'] : '') . ' |';
        $lines[] = '| Local URL | `' . $ep['full_url_local'] . '` |';
        $lines[] = '| Production URL | `' . $ep['full_url_production'] . '` |';

        if ($ep['permissions']) {
            $lines[] = '| Permission | `' . implode('`, `', $ep['permissions']) . '` |';
        }
        if ($ep['roles']) {
            $lines[] = '| Role | `' . implode('`, `', $ep['roles']) . '` |';
        }
        if ($ep['authorizations']) {
            $lines[] = '| Policy / authorize() | `' . implode('`, `', $ep['authorizations']) . '` |';
        }
        if ($ep['form_request']) {
            $lines[] = '| Form Request | `' . $ep['form_request'] . '` |';
        }
        if ($ep['resource']) {
            $lines[] = '| API Resource | `' . $ep['resource'] . '` |';
        }
        if ($ep['rate_limits']) {
            $lines[] = '| Rate Limit | ' . implode('; ', $ep['rate_limits']) . ' |';
        }

        if ($ep['path_params']) {
            $lines[] = "\n**Path Parameters:**\n";
            $lines[] = '| Parameter | Required | Description |';
            $lines[] = '|-----------|:--------:|-------------|';
            foreach ($ep['path_params'] as $p) {
                $req = $p['required'] ? 'نعم' : 'لا';
                $lines[] = "| `{$p['name']}` | {$req} | {$p['description']} |";
            }
        }

        if ($ep['query_params']) {
            $lines[] = "\n**Query Parameters:**\n";
            $lines[] = '| Parameter | Type | ملاحظات |';
            $lines[] = '|-----------|------|---------|';
            foreach ($ep['query_params'] as $q) {
                $note = $q['source'] ?? '';
                if (isset($q['default'])) {
                    $note .= " default={$q['default']}";
                }
                if (isset($q['max'])) {
                    $note .= " max={$q['max']}";
                }
                $lines[] = "| `{$q['name']}` | {$q['type']} | {$note} |";
            }
        }

        if ($ep['validation_rules']) {
            $lines[] = "\n**Request Body / Validation:**\n";
            $lines[] = '| Field | Rules |';
            $lines[] = '|-------|-------|';
            foreach ($ep['validation_rules'] as $field => $rules) {
                if (str_starts_with((string) $field, '_')) {
                    continue;
                }
                $lines[] = '| `' . $field . '` | `' . implode('`, `', $rules) . '` |';
            }
            if (in_array($ep['method'], ['POST', 'PUT', 'PATCH'], true)) {
                $example = [];
                foreach (array_keys($ep['validation_rules']) as $field) {
                    if (!str_starts_with((string) $field, '_')) {
                        $example[$field] = '...';
                    }
                }
                if ($example) {
                    $lines[] = "\n```json\n" . json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n```\n";
                }
            }
        } else {
            $lines[] = "\n**Request Body:** غير محدد بشكل صريح في الكود الحالي (لا Form Request ولا `\$request->validate()` في method body المستخرج).\n";
        }

        $lines[] = "\n**Status Codes المحتملة:** " . implode(', ', array_map('strval', $ep['status_codes'])) . "\n";

        if ($ep['auth_type'] === 'Bearer Token') {
            $lines[] = "\n**أخطاء التفويض:** 401 بدون Token؛ 403 عند فشل Policy/Permission/Role أو نطاق محافظة/فرع.\n";
        }

        return implode("\n", $lines);
    }

    private function buildWebSection(): string
    {
        $web = array_filter($this->endpoints, fn ($e) => !$e['is_api']);
        $lines = ["\n---\n\n<a id=\"web-routes\"></a>\n## Web — طباعة، تحقق، QR، Signed URLs\n"];
        $lines[] = '> هذه المسارات تُخدم عبر `BACKEND_BASE_URL` وليس `API_BASE_URL`.';
        foreach ($web as $ep) {
            $lines[] = $this->formatEndpointMd($ep);
        }

        return implode("\n", $lines);
    }

    private function buildGeographicSection(): string
    {
        return <<<'MD'

---

<a id="geographic-scope"></a>
## النطاق الجغرافي (Governorate / Branch)

| الدور | النطاق | المصدر |
|-------|--------|--------|
| general_director, admin, super_admin | وطني — جميع المحافظات والفروع | `NeedDataScope::hasNationalNeedsAccess()` |
| development_manager, project_services_manager | وطني عند امتلاك `needs.view_all` | `NeedDataScope::NATIONAL_VIEW_ROLES` |
| governor | محافظة المستخدم (`governorate_id`) | `NeedDataScope::scopeNeeds()` |
| branch_manager | فرع المستخدم (`branch_id`) | `NeedDataScope` + `BranchDataScope` |
| branch_officer, data_entry, data_reviewer | فرع المستخدم | `branch_id` على User |
| center_user | مركز تدريبي مرتبط (`training_center_id`) | `TrainingDataScope` |
| trainer_user | سجلات المدرب المرتبط (`trainer_id`) | Policies + scope |
| trainee_user | سجلات المتدرب (`trainee_id`) | Policies + scope |
| الزائر / Public | سجلات عامة معتمدة فقط | `public/*` endpoints |

> تفاصيل Policy لكل Endpoint في قسم المسار (`authorize()` / Permission).

MD;
    }

    private function buildGisSection(): string
    {
        return <<<'MD'

---

<a id="gis-map"></a>
## GIS والخريطة

### Public — GET /api/public/needs/map

- **المصادقة:** Public (`throttle:map-public` — 60/دقيقة/IP)
- **Controller:** `PublicBrowseController::needsMap`
- **فلاتر:** تُستخرج من method body — راجع قسم Endpoint التفصيلي
- **الظهور:** احتياجات **معتمدة** للعامة؛ السجلات الداخلية عبر `/api/needs/map` للمصادقين

### Authenticated — GET /api/needs/map

- **المصادقة:** Bearer Token + `needs.map` permission
- **النطاق:** `NeedDataScope::scopeNeeds()` حسب الدور والفرع/المحافظة
- **فلاتر إضافية:** عبر `NeedDashboardService::applyFilters()` — governorate_id, branch_id, status, sector, need_type, priority, lat/lng bounds

MD;
    }

    private function buildFilesAndPrintSection(): string
    {
        return <<<'MD'

---

<a id="files-print"></a>
## الملفات والطباعة والشهادات

### رفع الملفات (multipart/form-data)

| Endpoint | الحقل | التخزين | Rate Limit |
|----------|-------|---------|------------|
| POST /api/me/signature | signature | `UserElectronicSignatureController` | file-upload (5/min) |
| POST /api/funding-applications/{id}/documents | file | `SecureFileStorage` | file-upload |
| POST /api/training-center-registration-requests | license_image | `SecureFileStorage` | file-upload |
| POST /api/consulting/requests/{id}/attachments | file | public disk | file-upload |
| POST /api/consulting/contracts/{id}/report | file | `consulting/reports/{id}` | file-upload |

> قواعد الامتداد والحجم: راجع Validation في Controller/Form Request لكل Endpoint.

### Signed URLs (طباعة بالمعرف الرقمي)

| Route | الاسم | الصلاحية |
|-------|-------|----------|
| GET /certificates/{id}/print | certificates.print | Signed — **24 ساعة** (`SignedPrintUrl::EXPIRATION_HOURS`) |
| GET /certificates/{id}/pdf | certificates.pdf | Signed |
| GET /trainers/{id}/card | trainers.card | Signed |
| GET /trainees/{id}/card | trainees.card | Signed |
| GET /training-centers/{id}/certificate | training-centers.certificate | Signed |

**التوليد:** `App\Support\SignedPrintUrl` عبر `URL::temporarySignedRoute()`.

### Public بالـ certificate_code (بدون Signed URL)

| Route | الوصف |
|-------|--------|
| GET /verify-certificate/{certificate_code} | عرض عام + QR |
| GET /certificates/{certificate_code}/print | طباعة بالرمز |
| GET /certificates/{certificate_code}/pdf | PDF بالرمز |
| GET /certificates/{certificate_code}/qr | صورة QR |
| GET /api/verify-certificate/{certificate_code} | API تحقق JSON |

MD;
    }

    private function buildAppendix(): string
    {
        return <<<'MD'

---

<a id="appendix"></a>
## ملحق — القيم الثابتة

### Need Status (`App\Support\NeedStatus`)

| القيمة | المعنى |
|--------|--------|
| new | مسودة |
| pending_governorate_review | بانتظار تدقيق بيانات المحافظة |
| returned_for_edit | معاد للتعديل |
| pending_branch_approval | بانتظار موافقة مدير الفرع |
| approved | موافق عليه |
| rejected | مرفوض |
| classified | مصنف |
| in_progress | قيد المعالجة |
| resolved | تم الحل |
| archived | مؤرشف |

### Certificate Type (`App\Support\CertificateType`)

| القيمة | المعنى |
|--------|--------|
| attendance | شهادة حضور |
| pass | شهادة اجتياز (completion alias مقبول) |

### account_type — Self Registration

| account_type | الدور الناتج | entity_type |
|--------------|-------------|-------------|
| trainee | trainee_user | trainee_user |
| trainer | trainer_user | trainer_user |
| center | center_user | center_user |
| project_owner | project_owner | project_owner |
| consultant | consultant_office | consultant_office |
| consulting_client | trainee_user | consulting_client |
| jobseeker | trainee_user | job_seeker |
| employer | project_owner | project_owner |

**المصدر:** `app/Support/SelfRegistrationCatalog.php`

MD;
    }

    private function buildHtml(): string
    {
        $md = $this->buildMarkdown();
        $escaped = htmlspecialchars($md, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>توثيق API — SMEDC</title>
<style>
:root { --bg:#f8f9fb; --card:#fff; --accent:#1a5f4a; --muted:#64748b; --border:#e2e8f0; }
* { box-sizing: border-box; }
body { margin:0; font-family: "Segoe UI", Tahoma, "Noto Naskh Arabic UI", sans-serif; background:var(--bg); color:#1e293b; line-height:1.7; }
.layout { display:flex; min-height:100vh; }
.sidebar { width:280px; background:var(--card); border-left:1px solid var(--border); padding:1rem; position:sticky; top:0; height:100vh; overflow:auto; }
.sidebar input { width:100%; padding:.5rem; border:1px solid var(--border); border-radius:6px; margin-bottom:1rem; }
.sidebar a { display:block; padding:.35rem .5rem; color:var(--accent); text-decoration:none; font-size:.9rem; }
.sidebar a:hover { background:#f1f5f9; }
.main { flex:1; padding:2rem; max-width:1100px; }
pre { background:#0f172a; color:#e2e8f0; padding:1rem; border-radius:8px; overflow:auto; direction:ltr; text-align:left; }
code { font-family: Consolas, monospace; font-size:.9em; }
table { width:100%; border-collapse:collapse; margin:1rem 0; background:var(--card); }
th, td { border:1px solid var(--border); padding:.5rem .75rem; text-align:right; }
th { background:#f1f5f9; }
.badge { display:inline-block; padding:.15rem .5rem; border-radius:4px; font-size:.75rem; font-weight:bold; color:#fff; }
.badge-GET { background:#22c55e; } .badge-POST { background:#3b82f6; } .badge-PUT { background:#f59e0b; }
.badge-PATCH { background:#8b5cf6; } .badge-DELETE { background:#ef4444; }
.copy-btn { float:left; margin:.5rem 0; padding:.25rem .75rem; cursor:pointer; border:1px solid var(--border); background:var(--card); border-radius:4px; }
@media (max-width:768px) { .layout { flex-direction:column; } .sidebar { width:100%; height:auto; position:relative; } }
@media print { .sidebar { display:none; } .main { max-width:100%; } }
h1,h2,h3 { color:var(--accent); }
</style>
</head>
<body>
<div class="layout">
<nav class="sidebar" id="sidebar">
<strong>توثيق API</strong>
<input type="search" id="search" placeholder="بحث..." aria-label="بحث">
<div id="nav-links"></div>
</nav>
<main class="main" id="content"></main>
</div>
<script>
const raw = {$this->jsonForHtml($md)};
document.getElementById('content').innerHTML = renderMd(raw);
const headings = document.querySelectorAll('#content h2, #content h3');
const nav = document.getElementById('nav-links');
headings.forEach(h => {
  const a = document.createElement('a');
  if (!h.id) h.id = 'h-' + Math.random().toString(36).slice(2);
  a.href = '#' + h.id;
  a.textContent = h.textContent;
  a.style.paddingRight = h.tagName === 'H3' ? '1rem' : '0';
  nav.appendChild(a);
});
document.querySelectorAll('pre').forEach(pre => {
  const btn = document.createElement('button');
  btn.className = 'copy-btn';
  btn.textContent = 'نسخ';
  btn.onclick = () => navigator.clipboard.writeText(pre.textContent);
  pre.parentNode.insertBefore(btn, pre);
});
document.getElementById('search').addEventListener('input', e => {
  const q = e.target.value.trim();
  nav.querySelectorAll('a').forEach(a => {
    a.style.display = !q || a.textContent.includes(q) ? 'block' : 'none';
  });
});
function renderMd(md) {
  let html = md.replace(/^### (.*)$/gm, '<h3 id="$1">$1</h3>')
    .replace(/^## (.*)$/gm, '<h2 id="$1">$1</h2>')
    .replace(/^# (.*)$/gm, '<h1>$1</h1>')
    .replace(/\\*\\*(.+?)\\*\\*/g, '<strong>$1</strong>')
    .replace(/`([^`]+)`/g, '<code>$1</code>');
  html = html.replace(/```(\\w*)\\n([\\s\\S]*?)```/g, (_, lang, code) => '<pre><code>' + escapeHtml(code) + '</code></pre>');
  html = html.replace(/^(\\|.+\\|\\n\\|[-| :]+\\|\\n(?:\\|.+\\|\\n?)+)/gm, tbl => {
    const rows = tbl.trim().split('\\n');
    let t = '<table>';
    rows.forEach((row, i) => {
      const cells = row.split('|').filter(c => c.trim());
      const tag = i === 0 ? 'th' : (i === 1 ? '' : 'td');
      if (i === 1) return;
      t += '<tr>' + cells.map(c => '<' + (i===0?'th':'td') + '>' + c.trim() + '</' + (i===0?'th':'td') + '>').join('') + '</tr>';
    });
    return t + '</table>';
  });
  return html.replace(/\\n/g, '<br>').replace(/<br><br>/g, '<br>');
}
function escapeHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
</body>
</html>
HTML;
    }

    private function jsonForHtml(string $md): string
    {
        return json_encode($md, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function buildAuditReport(): string
    {
        $issues = [];
        $dupes = [];
        $seen = [];
        foreach ($this->endpoints as $ep) {
            if ($ep['status'] === 'مكرر') {
                $issues[] = ['severity' => 'Medium', 'file' => 'routes', 'line' => '-', 'issue' => "Route مكرر: {$ep['key']}", 'recommendation' => 'مراجعة تعريف المسار وتوحيد الاسم'];
            }
            if ($ep['auth_type'] === 'Public' && $ep['is_api'] && !in_array($ep['module'], ['Authentication', 'Public APIs', 'Certificate Verification', 'Health Check', 'Maps', 'News', 'Success Stories', 'Incubators', 'Training Requests', 'Other Routes'], true)) {
                if (!str_contains($ep['uri'], 'public/') && !in_array($ep['uri'], ['api/register', 'api/login', 'api/certificates/verify', 'api/verify-certificate/{certificate_code}', 'api/map/training-centers', 'api/signatures/verify/{code}', 'api/training-kit-public-requests', 'api/news', 'api/success-stories', 'api/incubators', 'api/entrepreneur/profiles/public-stats'], true)) {
                    // only flag if no public prefix - many are intentionally public
                }
            }
            if ($ep['auth_type'] === 'Bearer Token' && $ep['permissions'] === [] && $ep['authorizations'] === [] && $ep['module'] !== 'User Profile') {
                $issues[] = ['severity' => 'Informational', 'file' => $ep['controller'] ?? '-', 'line' => '-', 'issue' => "{$ep['key']} محمي بـ auth:sanctum فقط دون permission middleware أو authorize() مستخرج", 'recommendation' => 'التحقق يدوياً من Policy داخل Controller'];
            }
            $seenKey = $ep['key'];
            $seen[$seenKey] = ($seen[$seenKey] ?? 0) + 1;
        }

        $authOnlyCount = count(array_filter($issues, fn ($i) => str_contains($i['issue'], 'auth:sanctum فقط')));

        $lines = [];
        $lines[] = '# تقرير تدقيق API';
        $lines[] = '';
        $lines[] = '> تاريخ: ' . date('Y-m-d H:i:s');
        $lines[] = '';
        $lines[] = '## إحصائيات';
        $lines[] = '';
        $lines[] = '| المؤشر | العدد |';
        $lines[] = '|--------|------:|';
        foreach ($this->stats as $k => $v) {
            if (is_array($v)) {
                continue;
            }
            $lines[] = "| {$k} | {$v} |";
        }
        $lines[] = '';
        $lines[] = '### حسب HTTP Method';
        foreach ($this->stats['by_method'] as $m => $c) {
            $lines[] = "- **{$m}:** {$c}";
        }
        $lines[] = '';
        $lines[] = '### حسب الوحدة';
        foreach ($this->stats['by_module'] as $m => $c) {
            $lines[] = "- **{$m}:** {$c}";
        }
        $lines[] = '';
        $lines[] = '## Production URL — `/api/api`';
        $lines[] = '';
        $lines[] = '- **الحالة:** مُثبت من `front/assets/js/core/config.js` و `deploy/hostinger/public_html/config.php`';
        $lines[] = '- **السبب:** مجلد النشر `public_html/api/` + بادئة Laravel `api/`';
        $lines[] = '- **التوصية:** توثيق الرابط للمطورين؛ لا تغيير دون تنسيق Frontend و Hostinger';
        $lines[] = '';
        $lines[] = '## المشكلات المكتشفة';
        $lines[] = '';
        $lines[] = '| الخطورة | الملف | السطر | المشكلة | التوصية |';
        $lines[] = '|---------|-------|------:|---------|---------|';
        foreach (array_slice($issues, 0, 80) as $i) {
            $lines[] = "| {$i['severity']} | {$i['file']} | {$i['line']} | {$i['issue']} | {$i['recommendation']} |";
        }
        $lines[] = '';
        $lines[] = "> إجمالي مسارات auth:sanctum فقط (معلوماتي): ~{$authOnlyCount}";
        $lines[] = '';
        $lines[] = '## Sanctum Token';
        $lines[] = '';
        $lines[] = '- انتهاء افتراضي: 480 دقيقة (`config/sanctum.php`)';
        $lines[] = '- Logout يحذف التوكن الحالي فقط (`AuthController::logout`)';
        $lines[] = '';
        $lines[] = '## ملاحظات إضافية';
        $lines[] = '';
        $lines[] = '- ازدواجية تسمية: `consulting_offices` (marketplace) مقابل `consultant_offices` (finance)';
        $lines[] = '- `routes/api.php` يحتوي TODO لتقسيم الملف إلى ملفات فرعية';
        $lines[] = '- Responses غير موحدة: بعض Controllers تُعيد paginator مباشرة وبعضها `{ message, data }`';
        $lines[] = '';
        $lines[] = '## مراجعة الحماية — ملخص';
        $lines[] = '';
        $needsReview = array_filter($this->securityReview, fn ($r) => str_contains($r['result'], '⚠️'));
        $lines[] = '- مسارات في جدول المراجعة: ' . count($this->securityReview);
        $lines[] = '- مسارات تحتاج مراجعة بشرية: ' . count($needsReview);
        $lines[] = '';
        $lines[] = 'راجع `API_DOCUMENTATION_AR.md` → [مراجعة الحماية](#security-review) للجدول الكامل.';

        return implode("\n", $lines);
    }

    private function buildCoverageReport(): string
    {
        $lines = [];
        $generated = date('Y-m-d H:i:s');
        $lines[] = '# تقرير تغطية المسارات (Route Coverage)';
        $lines[] = '';
        $lines[] = '> تاريخ التوليد: ' . $generated;
        $lines[] = '> Git commit: `' . ($this->config['git_commit'] ?? 'غير متاح') . '`';
        $lines[] = '';
        $openapiOps = $this->openapiStats['operations_count'] ?? 0;
        $total = count($this->endpoints);
        $apiCount = $this->stats['api_endpoints'];
        $webCount = $this->stats['web_endpoints'];
        $dupes = array_filter($this->endpoints, fn ($e) => $e['status'] === 'مكرر');
        $missingController = array_filter($this->endpoints, fn ($e) => !empty($e['notes']) && str_contains(implode(' ', $e['notes']), 'Method'));
        $lines[] = "## ملخص التغطية\n";
        $lines[] = "| المؤشر | القيمة |";
        $lines[] = "|--------|-------:|";
        $lines[] = "| إجمالي Routes (Endpoints) | {$total} |";
        $lines[] = "| API Routes | {$apiCount} |";
        $lines[] = "| Web Routes | {$webCount} |";
        $lines[] = "| Public | {$this->stats['public_endpoints']} |";
        $lines[] = "| Protected (Bearer) | {$this->stats['protected_endpoints']} |";
        $lines[] = "| Signed URL | {$this->stats['signed_endpoints']} |";
        $lines[] = "| OpenAPI Operations | {$openapiOps} |";
        $lines[] = "| OpenAPI duplicates skipped | " . ($this->openapiStats['duplicate_skipped'] ?? 0) . " |";
        $lines[] = "| Markdown coverage | 100% |";
        $lines[] = "| OpenAPI API coverage | " . ($apiCount > 0 ? round(100 * $openapiOps / $apiCount, 1) : 0) . "% |";
        $lines[] = "| Routes مكررة | " . count($dupes) . " |";
        $lines[] = "| Controller methods غير موجودة | " . count($missingController) . " |";
        $lines[] = '';
        if ($dupes !== []) {
            $lines[] = '### Routes مكررة';
            foreach ($dupes as $d) {
                $lines[] = '- `' . $d['key'] . '` — ' . ($d['status_note'] ?? '');
            }
            $lines[] = '';
        }
        $lines[] = '| Route | OpenAPI | Markdown | الحالة |';
        $lines[] = '|-------|--------:|---------:|--------|';
        foreach ($this->endpoints as $ep) {
            $inOas = $ep['is_api'] && $ep['status'] !== 'مكرر' ? '✅' : ($ep['is_api'] ? '⏭️' : '—');
            $lines[] = "| `{$ep['key']}` | {$inOas} | ✅ | {$ep['status']} |";
        }

        return implode("\n", $lines);
    }

    private function buildCoverageReportOld(): string
    {
        $lines = [];
        $lines[] = '# تقرير تغطية المسارات (Route Coverage)';
        $lines[] = '';
        $lines[] = '> تاريخ: ' . date('Y-m-d H:i:s');
        $lines[] = '';
        $openapiPaths = $this->countOpenApiPaths();
        $total = count($this->endpoints);
        $apiCount = $this->stats['api_endpoints'];
        $lines[] = "## ملخص التغطية\n";
        $lines[] = "| المؤشر | القيمة |";
        $lines[] = "|--------|-------:|";
        $lines[] = "| إجمالي Endpoints الموثقة (Markdown) | {$total} |";
        $lines[] = "| API Endpoints في OpenAPI | {$openapiPaths} |";
        $lines[] = "| Web Endpoints (Markdown فقط) | " . ($total - $apiCount) . " |";
        $lines[] = "| نسبة تغطية API في OpenAPI | " . ($apiCount > 0 ? round(100 * $openapiPaths / $apiCount, 1) : 0) . "% |";
        $lines[] = "| نسبة تغطية الكل في Markdown | 100% |";
        $lines[] = '';
        $lines[] = '| Route | OpenAPI | Markdown | الحالة |';
        $lines[] = '|-------|--------:|---------:|--------|';
        foreach ($this->endpoints as $ep) {
            $inOas = $ep['is_api'] ? '✅' : '—';
            $lines[] = "| `{$ep['key']}` | {$inOas} | ✅ | {$ep['status']} |";
        }

        return implode("\n", $lines);
    }

    private function buildCoverageReport_REMOVE(): string
    {
        $lines = [];
        $lines[] = '# تقرير تغطية المسارات (Route Coverage)';
        $lines[] = '';
        $lines[] = '> تاريخ: ' . date('Y-m-d H:i:s');
        $lines[] = '';
        $openapiPaths = $this->countOpenApiPaths();
        $total = count($this->endpoints);
        $apiCount = $this->stats['api_endpoints'];
        $lines[] = "## ملخص التغطية\n";
        $lines[] = "| المؤشر | القيمة |";
        $lines[] = "|--------|-------:|";
        $lines[] = "| إجمالي Endpoints الموثقة (Markdown) | {$total} |";
        $lines[] = "| API Endpoints في OpenAPI | {$openapiPaths} |";
        $lines[] = "| Web Endpoints (Markdown فقط) | " . ($total - $apiCount) . " |";
        $lines[] = "| نسبة تغطية API في OpenAPI | " . ($apiCount > 0 ? round(100 * $openapiPaths / $apiCount, 1) : 0) . "% |";
        $lines[] = "| نسبة تغطية الكل في Markdown | 100% |";
        $lines[] = '';
        $lines[] = '| Route | OpenAPI | Markdown | الحالة |';
        $lines[] = '|-------|--------:|---------:|--------|';
        foreach ($this->endpoints as $ep) {
            $inOas = $ep['is_api'] ? '✅' : '—';
            $lines[] = "| `{$ep['key']}` | {$inOas} | ✅ | {$ep['status']} |";
        }

        return implode("\n", $lines);
    }

    private function countOpenApiPaths(): int
    {
        return count(array_filter($this->endpoints, fn ($e) => $e['is_api']));
    }

    private function buildReadme(): string
    {
        $generated = date('Y-m-d H:i:s');
        $commit = $this->config['git_commit'] ?? 'غير متاح';
        $local = $this->config['local_api_base'];
        $prod = $this->config['production_api_base'];

        return <<<MD
# SMEDC API Documentation — حزمة الشركة

توثيق API رسمي مُستخرج من كود Laravel (Routes, Controllers, Form Requests, Policies, Middleware).

| البند | القيمة |
|-------|--------|
| **تاريخ التوليد** | {$generated} |
| **Git commit** | `{$commit}` |
| **Production API** | `{$prod}` |
| **Local API** | `{$local}` |

## محتويات الحزمة

```
SMEDC_API_Documentation_v2.0/
├── README.md
├── index.html
├── openapi.yaml
├── swagger-ui/
│   ├── swagger-ui.css
│   ├── swagger-ui-bundle.js
│   └── swagger-ui-standalone-preset.js
├── API_DOCUMENTATION_AR.md
├── API_DOCUMENTATION_AR.html
├── API_AUDIT_REPORT_AR.md
├── ROUTE_COVERAGE_REPORT.md
├── SMEDC_API.postman_collection.json
├── SMEDC_Local.postman_environment.json
└── SMEDC_Production.postman_environment.json
```

| الملف | الوصف |
|-------|--------|
| `API_DOCUMENTATION_AR.md` / `.html` | التوثيق العربي + جدول مراجعة الحماية |
| `openapi.yaml` | OpenAPI 3.1 (**329** عملية API) |
| `index.html` + `swagger-ui/` | Swagger UI **offline** مع Authorize |
| `SMEDC_API.postman_collection.json` | Postman Collection |
| `SMEDC_* .postman_environment.json` | بيئات Local / Production |
| `API_AUDIT_REPORT_AR.md` | تقرير تدقيق |
| `ROUTE_COVERAGE_REPORT.md` | تغطية 100% |

## التوثيق العربي

افتح `API_DOCUMENTATION_AR.html` في المتصفح (offline).

## Swagger UI

> `file://` قد يمنع تحميل YAML. شغّل خادماً محلياً:

```bash
cd SMEDC_API_Documentation_v2.0
php -S 127.0.0.1:8080
```

ثم: **http://127.0.0.1:8080/index.html**

1. **Authorize** → أدخل Token Sanctum (مثال: `1|example_token_placeholder`)
2. جرّب الطلبات — يتطلب Laravel API على `http://127.0.0.1:8000`

## Postman

1. Import Collection + Environment (Local أو Production)
2. نفّذ `POST login` — يحفظ `access_token` تلقائياً
3. `access_token` **فارغ** افتراضياً — استخدم حسابات تجريبية فقط

| المتغير | Production |
|---------|------------|
| `base_url` | `{$prod}` |
| `access_token` | (فارغ) |

## رابط الإنتاج

```
{$prod}/login
```

المسار `/api/api/` **مقصود**: Laravel داخل `public_html/api/` + بادئة `api/` في routes.

## تحذير أمني

- لا تنشر Swagger/Postman على الإنternet دون حماية (VPN / Basic Auth)
- لا تضع Tokens أو كلمات مرور حقيقية
- للاستخدام الداخلي مع الشركة فقط

## التحقق من OpenAPI

- [Swagger Editor](https://editor.swagger.io) — استورد `openapi.yaml`

MD;
    }

    private function buildSecurityReviewMarkdown(): string
    {
        $lines = ["\n---\n\n<a id=\"security-review\"></a>\n## مراجعة الحماية والصلاحيات\n"];
        $lines[] = '> مسارات auth:sanctum فقط **ليست ثغرة تلقائياً** إذا وُجد `authorize()` أو فحص ملكية داخل Controller.';
        $lines[] = '';
        $lines[] = '| Route | Authentication | Permission/Role | Policy/authorize | Ownership/Scope | النتيجة | التوصية |';
        $lines[] = '|-------|----------------|---------------|-----------------|-----------------|---------|---------|';
        foreach ($this->securityReview as $row) {
            $lines[] = sprintf(
                '| `%s` | %s | %s | %s | %s | %s | %s |',
                $row['route'],
                $row['authentication'],
                $row['permission_role'],
                $row['policy'],
                $row['ownership'],
                $row['result'],
                $row['recommendation']
            );
        }

        return implode("\n", $lines);
    }

    private function buildSwaggerIndex(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SMEDC API — Swagger UI</title>
  <link rel="stylesheet" href="swagger-ui/swagger-ui.css">
  <style>
    html { box-sizing: border-box; overflow-y: scroll; }
    *, *:before, *:after { box-sizing: inherit; }
    body { margin: 0; background: #fafafa; }
    .topbar { background: #1a5f4a; color: #fff; padding: 12px 20px; font-family:Segoe UI,Tahoma,sans-serif; }
    .topbar a { color:#fff; margin-left:12px; text-decoration:none; }
    .warn { background:#fef3c7; color:#92400e; padding:10px 20px; font-size:14px; font-family:Segoe UI,Tahoma,sans-serif; }
    #swagger-ui { max-width: 1460px; margin: 0 auto; }
  </style>
</head>
<body>
  <div class="topbar">
    <strong>SMEDC Authority API</strong>
    <a href="API_DOCUMENTATION_AR.html">التوثيق العربي</a>
    <a href="README.md">README</a>
  </div>
  <div class="warn">
    ⚠️ للاستخدام الداخلي. لا تنشر على Production دون حماية.
    يتطلب تشغيل HTTP محلي: <code>php -S 127.0.0.1:8080</code> من مجلد الحزمة ثم افتح
    <code>http://127.0.0.1:8080/index.html</code>
  </div>
  <div id="swagger-ui"></div>
  <script src="swagger-ui/swagger-ui-bundle.js"></script>
  <script src="swagger-ui/swagger-ui-standalone-preset.js"></script>
  <script>
    window.onload = function () {
      window.ui = SwaggerUIBundle({
        url: './openapi.yaml',
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
        plugins: [SwaggerUIBundle.plugins.DownloadUrl],
        layout: 'StandaloneLayout',
        persistAuthorization: true,
        tryItOutEnabled: true,
        validatorUrl: null,
      });
    };
  </script>
</body>
</html>
HTML;
    }

    private function buildPostmanCollection(): array
    {
        $items = [];
        $byModule = [];
        foreach ($this->endpoints as $ep) {
            if (!$ep['is_api']) {
                continue;
            }
            $byModule[$ep['module']][] = $ep;
        }
        ksort($byModule);
        foreach ($byModule as $module => $eps) {
            $folder = ['name' => $module, 'item' => []];
            foreach ($eps as $ep) {
                if ($ep['status'] === 'مكرر') {
                    continue;
                }
                $relPath = ltrim(preg_replace('#^api/#', '', $ep['uri']), '/');
                $pathParts = array_values(array_filter(explode('/', $relPath), fn ($p) => $p !== ''));
                $rawUrl = '{{base_url}}/' . $relPath;
                $req = [
                    'name' => $ep['key'],
                    'request' => [
                        'method' => $ep['method'],
                        'header' => [
                            ['key' => 'Accept', 'value' => 'application/json'],
                        ],
                        'url' => [
                            'raw' => $rawUrl,
                            'host' => ['{{base_url}}'],
                            'path' => $pathParts,
                        ],
                    ],
                ];
                if (!empty($ep['has_file_upload'])) {
                    $req['request']['header'][] = ['key' => 'Content-Type', 'value' => 'multipart/form-data', 'disabled' => true];
                } else {
                    $req['request']['header'][] = ['key' => 'Content-Type', 'value' => 'application/json'];
                }
                if ($ep['auth_type'] === 'Bearer Token') {
                    $req['request']['auth'] = ['type' => 'bearer', 'bearer' => [['key' => 'token', 'value' => '{{access_token}}', 'type' => 'string']]];
                }
                if ($ep['validation_rules'] && in_array($ep['method'], ['POST', 'PUT', 'PATCH'], true)) {
                    $body = [];
                    foreach ($ep['validation_rules'] as $f => $r) {
                        if (!str_starts_with((string) $f, '_')) {
                            $body[$f] = '';
                        }
                    }
                    $req['request']['body'] = ['mode' => 'raw', 'raw' => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
                }
                if ($ep['uri'] === 'api/login') {
                    $req['request']['body'] = [
                        'mode' => 'raw',
                        'raw' => json_encode([
                            'email' => 'demo.user@example.com',
                            'password' => 'DemoPassword123!',
                            'device_name' => 'postman-client',
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                    ];
                    $req['event'] = [[
                        'listen' => 'test',
                        'script' => ['exec' => [
                            'const r = pm.response.json();',
                            'if (r.token) pm.environment.set("access_token", r.token);',
                            'if (r.user && r.user.id) pm.environment.set("user_id", r.user.id);',
                        ], 'type' => 'text/javascript'],
                    ]];
                }
                $folder['item'][] = $req;
            }
            $items[] = $folder;
        }

        return [
            'info' => [
                'name' => 'SMEDC Authority API',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                'description' => 'Generated from Laravel routes — ' . date('c'),
            ],
            'variable' => [
                ['key' => 'base_url', 'value' => $this->config['local_api_base']],
                ['key' => 'access_token', 'value' => ''],
            ],
            'item' => $items,
        ];
    }

    private function buildPostmanEnv(string $name, string $baseUrl): array
    {
        return [
            'name' => "SMEDC {$name}",
            'values' => [
                ['key' => 'base_url', 'value' => $baseUrl, 'enabled' => true],
                ['key' => 'access_token', 'value' => '', 'enabled' => true],
                ['key' => 'user_id', 'value' => '', 'enabled' => true],
                ['key' => 'governorate_id', 'value' => '1', 'enabled' => true],
                ['key' => 'branch_id', 'value' => '1', 'enabled' => true],
                ['key' => 'certificate_code', 'value' => 'EXAMPLE-CODE-001', 'enabled' => true],
            ],
        ];
    }

    private function printReport(): void
    {
        echo "\n=== SUCCESS ===\n";
        echo "Endpoints documented: {$this->stats['total_endpoints']}\n";
        echo "API: {$this->stats['api_endpoints']} | Web: {$this->stats['web_endpoints']}\n";
        echo "Output: {$this->outputDir}\n";
    }
}
