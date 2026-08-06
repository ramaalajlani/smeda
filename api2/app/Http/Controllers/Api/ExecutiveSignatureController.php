<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Signing\ElectronicSignatureService;
use Illuminate\Http\JsonResponse;

class ExecutiveSignatureController extends Controller
{
    public function __construct(private ElectronicSignatureService $signatures) {}

    /** GET /signatures/verify/{code} — تحقق عام من التوقيع الإلكتروني */
    public function verify(string $code): JsonResponse
    {
        $result = $this->signatures->verify($code);

        if (!$result) {
            return response()->json([
                'valid' => false,
                'message' => 'رمز التوقيع غير صالح أو غير موجود.',
            ], 404);
        }

        return response()->json($result);
    }
}
