<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // كل الصلاحيات الممكنة في النظام
        $allPermissions = [
            'create_estimation',
            'price_estimation',
            'approve_estimation',
        ];

        // اجلب ما في الـ permissions من الموديل واطبّعه كـ array
        $raw = $this->permissions;

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $userPermissions = is_array($decoded) ? $decoded : [];
        } elseif (is_object($raw)) {
            $userPermissions = (array) $raw;
        } elseif (is_array($raw)) {
            $userPermissions = $raw;
        } else {
            $userPermissions = [];
        }

        // Normalize: نحول أي شكل (قائمة أو خريطة) إلى خريطة key => boolean
        $normalized = [];

        foreach ($userPermissions as $k => $v) {
            if (is_int($k)) {
                // الحالة: array بشكل قائمة ["create_estimation", ...]
                $permName = trim((string)$v, "\"' ");
                $normalized[$permName] = true;
            } else {
                // الحالة: associative => "perm" => true/false or "true"/"false"
                $cleanKey = trim((string)$k, "\"' ");
                if (is_string($v)) {
                    // يحوّل "true"/"false" للنمط boolean
                    $boolVal = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    $normalized[$cleanKey] = $boolVal === null ? (bool)$v : $boolVal;
                } else {
                    $normalized[$cleanKey] = (bool)$v;
                }
            }
        }

        // الآن نرجّع الخريطة النهائية مرتّبة حسب جميع الصلاحيات الممكنة
        $permissionsMap = [];
        foreach ($allPermissions as $perm) {
            $permissionsMap[$perm] = $normalized[$perm] ?? false;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image,
            'role' => $this->role,
            'permissions' => $permissionsMap,
            'token' => $this->token ?? null,
        ];
    }
}
