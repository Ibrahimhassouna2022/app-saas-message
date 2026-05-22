<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Stancl\Tenancy\Database\Models\Domain;

class SuperAdminController extends Controller
{
    // عرض جميع الشركات
    public function index()
    {
        $tenants = Tenant::with('domains')->get();
        return response()->json([
            'tenants' => $tenants,
            'total' => $tenants->count(),
        ]);
    }
    
    // إنشاء شركة جديدة - قلب نظام الاشتراكات
    public function registerCompany(Request $request)
    {
        $validated = $request->validate([
            'company_slug' => 'required|string|unique:tenants,id',
            'company_name' => 'required|string',
            'admin_email' => 'required|email',
            'plan' => 'required|in:basic,premium,enterprise',
        ]);
        
        // 1. إنشاء الشركة في قاعدة البيانات المركزية
        $tenant = Tenant::create([
            'id' => $request->company_slug,
            'company_name' => $request->company_name,
            'admin_email' => $request->admin_email,
            'plan' => $request->plan,
            'max_messages' => $request->plan == 'basic' ? 1000 : ($request->plan == 'premium' ? 10000 : 100000),
            'subscription_ends_at' => now()->addYear(),
        ]);
        
        // 2. ربط الدومين (subdomain)
        $tenant->domains()->create([
            'domain' => $request->company_slug . '.saas.localhost'
        ]);
        
        // 3. تهيئة قاعدة بيانات الشركة وتشغيل Migrations الخاصة بها (تلقائي من الحزمة)
        
        return response()->json([
            'message' => "تم إنشاء شركة {$request->company_name} بنجاح",
            'tenant' => $tenant,
            'access_url' => "http://{$request->company_slug}.saas.localhost"
        ], 201);
    }
    
    // تحديث خطة اشتراك شركة
    public function updateSubscription(Request $request, $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        
        $tenant->update([
            'plan' => $request->plan,
            'max_messages' => $request->plan == 'basic' ? 1000 : ($request->plan == 'premium' ? 10000 : 100000),
            'subscription_ends_at' => $request->ends_at ?? now()->addYear(),
        ]);
        
        return response()->json([
            'message' => "تم تحديث اشتراك {$tenant->company_name} إلى خطة {$request->plan}",
            'tenant' => $tenant
        ]);
    }
    
    // حذف شركة (مع حذف قاعدة بياناتها)
    public function deleteCompany($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $companyName = $tenant->company_name;
        
        // الحزمة ستقوم تلقائياً بحذف قاعدة بيانات الشركة
        $tenant->delete();
        
        return response()->json([
            'message' => "تم حذف شركة {$companyName}"
        ]);
    }
}
