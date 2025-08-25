<?php

namespace App\Models;

use App\Models\Order;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'username',
        'phone',
        'email',
        'password',
        'otp',
        'image',
        'role',
        'permissions',
        'fcm_token',
    ];
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
    protected $casts = [
        'permissions' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public static function normalizePermissions($permissions, $allPermissions = [])
{
    $normalized = [];

    foreach ($permissions as $key => $value) {
        // تنظيف اسم البرميشن من أي ' أو "
        $cleanKey = preg_replace('/^["\']?(.*?)["\']?$/', '$1', trim($key));

        // تحويل القيمة لــ Boolean
        if (is_bool($value)) {
            $normalized[$cleanKey] = $value;
        } elseif (is_numeric($value)) {
            $normalized[$cleanKey] = ((int) $value) === 1;
        } elseif (is_string($value)) {
            $val = strtolower(trim($value, "\"' "));
            $normalized[$cleanKey] = in_array($val, ['1', 'true', 'yes', 'on'], true);
        } else {
            $normalized[$cleanKey] = false;
        }
    }

    // لو عاوز تضمن كل الـ permissions موجودة حتى لو مش مبعوتة
    if (!empty($allPermissions)) {
        $final = [];
        foreach ($allPermissions as $perm) {
            $final[$perm] = $normalized[$perm] ?? false;
        }
        return $final;
    }

    return $normalized;
}


}
