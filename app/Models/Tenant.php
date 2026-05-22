<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
    
    protected $guarded = [];
    
    protected $casts = [
        'subscription_ends_at' => 'date',
        'settings' => 'array',
    ];
    
    // الأعمدة المخصصة التي نضيفها إلى جدول tenants
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'plan',
            'company_name',
            'admin_email',
            'subscription_ends_at',
            'max_messages',
            'settings',
        ];
    }
}