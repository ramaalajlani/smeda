<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainingKitPublicRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingKitPublicRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_email' => ['required', 'email', 'max:255'],
            'proposed_name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $row = TrainingKitPublicRequest::query()->create(array_merge($validated, [
            'status' => 'pending',
        ]));

        return response()->json([
            'message' => 'تم استلام طلب ترشيح الحقيبة بنجاح. سيتم التواصل معك عبر البريد الإلكتروني.',
            'data' => $row,
        ], 201);
    }
}
