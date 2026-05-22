<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'is_read'
    ];

    // لا نحتاج للـ Trait هنا لأن الموديل يعمل دائماً داخل سياق الشركة (Tenant Context)
    // وقاعدة بيانات الشركة يتم تحويلها تلقائياً بواسطة الحزمة
}
