<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    private static function n(int $uid, string $type, string $title, string $icon, string $color, string $url = null, string $body = null, string $refType = null, int $refId = null): void
    {
        Notification::send($uid, $type, $title, array_filter([
            'body'           => $body,
            'icon'           => $icon,
            'color'          => $color,
            'action_url'     => $url,
            'action_label'   => $url ? 'عرض التفاصيل' : null,
            'reference_type' => $refType,
            'reference_id'   => $refId,
        ]));
    }

    /* ── طلبات الاستشارة ── */

    // لمدير الفرع: طلب جديد بانتظار الفرز
    public static function consultingRequestSubmitted(int $requestId, string $requestCode, string $title, int $governorateId): void
    {
        $url   = "/front/services/consulting/consulting-request-view.php?id={$requestId}";
        $managers = User::whereHas('roles', fn($q)=>$q->whereIn('name',['admin','super_admin','branch_manager']))
            ->where(fn($q)=>$q->where('governorate_id',$governorateId)->orWhereHas('roles',fn($q2)=>$q2->whereIn('name',['admin','super_admin'])))
            ->select('id')->get();
        foreach ($managers as $m) {
            static::n($m->id, 'consulting_submitted', "طلب استشارة جديد: {$requestCode}", 'bi-file-earmark-plus-fill', 'primary', $url, $title, 'consulting_request', $requestId);
        }
    }

    // لصاحب الطلب: تمت المراجعة (قبول أو طلب معلومات)
    public static function consultingRequestSorted(int $userId, int $requestId, string $requestCode, string $action): void
    {
        $url  = "/front/services/consulting/consulting-request-view.php?id={$requestId}";
        [$title, $icon, $color] = $action === 'approve'
            ? ["طلبك {$requestCode} قُبل وأُحيل للمكاتب", 'bi-check-circle-fill', 'success']
            : ["طلبك {$requestCode} يحتاج معلومات إضافية", 'bi-exclamation-circle-fill', 'warning'];
        static::n($userId, "consulting_sorted_{$action}", $title, $icon, $color, $url, null, 'consulting_request', $requestId);
    }

    // للمكاتب: طلب جديد في تخصصهم
    public static function consultingNewRequestForOffices(int $requestId, string $requestCode, string $categoryCode): void
    {
        $url = "/front/services/consulting/consulting-request-view.php?id={$requestId}";
        \App\Models\ConsultingOffice::where('status','active')
            ->whereHas('specializations', fn($q)=>$q->where('category_code',$categoryCode))
            ->whereNotNull('user_id')
            ->select('user_id')
            ->each(function($o) use ($requestId,$requestCode,$url,$categoryCode) {
                static::n($o->user_id, 'consulting_new_request', "طلب استشارة جديد ({$categoryCode}): {$requestCode}", 'bi-bell-fill', 'info', $url, null, 'consulting_request', $requestId);
            });
    }

    // لصاحب الطلب: عرض جديد من مكتب
    public static function consultingNewOffer(int $clientUserId, int $requestId, string $requestCode, string $officeName): void
    {
        static::n($clientUserId, 'consulting_new_offer', "عرض جديد من {$officeName} على طلبك {$requestCode}", 'bi-collection-fill', 'info',
            "/front/services/consulting/consulting-request-view.php?id={$requestId}", null, 'consulting_request', $requestId);
    }

    // للمكتب: تم قبول عرضه
    public static function consultingOfferAccepted(int $officeUserId, int $requestId, string $requestCode): void
    {
        static::n($officeUserId, 'consulting_offer_accepted', "تم قبول عرضك على الطلب {$requestCode}", 'bi-trophy-fill', 'success',
            "/front/services/consulting/consulting-request-view.php?id={$requestId}", null, 'consulting_request', $requestId);
    }

    // للمكتب: تم رفض عرضه
    public static function consultingOfferRejected(int $officeUserId, int $requestId, string $requestCode): void
    {
        static::n($officeUserId, 'consulting_offer_rejected', "لم يُقبل عرضك على الطلب {$requestCode}", 'bi-x-circle-fill', 'danger',
            "/front/services/consulting/consulting-request-view.php?id={$requestId}", null, 'consulting_request', $requestId);
    }

    // للطرفين: تم توقيع العقد
    public static function consultingContractSigned(int $userId, int $contractId, string $contractCode, string $signerRole): void
    {
        $label = $signerRole === 'client' ? 'صاحب المشروع' : 'المكتب';
        static::n($userId, 'consulting_contract_signed', "وقّع {$label} على العقد {$contractCode}", 'bi-pen-fill', 'success',
            "/front/services/consulting/consulting-request-view.php", null, 'consulting_contract', $contractId);
    }

    // للمدير: تقرير جديد بانتظار الاعتماد
    public static function consultingReportSubmitted(int $requestId, string $requestCode, int $governorateId): void
    {
        $url     = "/front/services/consulting/consulting-request-view.php?id={$requestId}";
        $managers = User::whereHas('roles', fn($q)=>$q->whereIn('name',['admin','super_admin','branch_manager']))
            ->where(fn($q)=>$q->where('governorate_id',$governorateId)->orWhereHas('roles',fn($q2)=>$q2->whereIn('name',['admin','super_admin'])))
            ->select('id')->get();
        foreach ($managers as $m) {
            static::n($m->id, 'consulting_report_submitted', "تقرير بانتظار الاعتماد: {$requestCode}", 'bi-file-earmark-pdf-fill', 'warning', $url, null, 'consulting_request', $requestId);
        }
    }

    // لصاحب الطلب والمكتب: تم اعتماد التقرير أو إعادته
    public static function consultingReportReviewed(int $userId, int $requestId, string $requestCode, string $action): void
    {
        $url = "/front/services/consulting/consulting-request-view.php?id={$requestId}";
        [$title, $icon, $color] = $action === 'approve'
            ? ["اعتُمد التقرير النهائي للطلب {$requestCode} ✓", 'bi-patch-check-fill', 'success']
            : ["أُعيد التقرير للمراجعة: {$requestCode}", 'bi-arrow-counterclockwise', 'warning'];
        static::n($userId, "consulting_report_{$action}", $title, $icon, $color, $url, null, 'consulting_request', $requestId);
    }

    // رسالة جديدة في العقد
    public static function consultingNewContractMessage(int $recipientId, int $requestId, string $senderName): void
    {
        static::n($recipientId, 'consulting_message', "رسالة جديدة من {$senderName}", 'bi-chat-text-fill', 'info',
            "/front/services/consulting/consulting-request-view.php?id={$requestId}", null, 'consulting_request', $requestId);
    }
}
