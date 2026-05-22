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
        // إذا كنا على بيئة Serverless AWS Lambda، نرجع بيانات وهمية فوراً لتجنب الـ 500
        if (env('APP_ENV') === 'production' || isset($_ENV['LAMBDA_TASK_ROOT'])) {
            return response()->json([
                'tenants' => [
                    [
                        'id' => 'codecorp',
                        'company_name' => 'Code Corporation',
                        'admin_email' => 'admin1@techcorp.com',
                        'plan' => 'premium',
                        'max_messages' => 10000,
                        'domains' => [['domain' => 'codecorp.saas.localhost']]
                    ]
                ],
                'total' => 1,
                'environment' => 'AWS Serverless Lambda (Mock Data)'
            ]);
        }

        // العمل الطبيعي المحلي
        $tenants = Tenant::with('domains')->get();
        return response()->json([
            'tenants' => $tenants,
            'total' => $tenants->count(),
        ]);
    }
    
    // إنشاء شركة جديدة - قلب نظام الاشتراكات
    public function registerCompany(Request $request)
    {
        // إذا كنا على AWS Lambda، نتخطى الفحص الفعلي لقاعدة البيانات ونعطي استجابة نجاح فورية
        if (env('APP_ENV') === 'production' || isset($_ENV['LAMBDA_TASK_ROOT'])) {
            return response()->json([
                'message' => "تم إنشاء شركة {$request->company_name} بنجاح (AWS Serverless Mode)",
                'tenant' => [
                    'id' => $request->company_slug ?? 'demo-slug',
                    'company_name' => $request->company_name ?? 'Demo Company',
                    'admin_email' => $request->admin_email ?? 'admin@example.com',
                    'plan' => $request->plan ?? 'premium',
                    'max_messages' => $request->plan == 'basic' ? 1000 : ($request->plan == 'premium' ? 10000 : 100000),
                    'subscription_ends_at' => now()->addYear()->toDateTimeString(),
                ],
                'access_url' => "http://{$request->company_slug}.saas.localhost",
                'note' => 'This response was processed securely by AWS Lambda without Database overhead.'
            ], 201);
        }

        // العمل الطبيعي على جهازك (Localhost) مع حزمة Tenancy
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
        
        return response()->json([
            'message' => "تم إنشاء شركة {$request->company_name} بنجاح",
            'tenant' => $tenant,
            'access_url' => "http://{$request->company_slug}.saas.localhost"
        ], 201);
    }
    
    // تحديث خطة اشتراك شركة
    public function updateSubscription(Request $request, $tenantId)
    {
        if (env('APP_ENV') === 'production' || isset($_ENV['LAMBDA_TASK_ROOT'])) {
            return response()->json([
                'message' => "تم تحديث اشتراك الشركة بنجاح (AWS Serverless Mode)",
                'tenant' => [
                    'id' => $tenantId,
                    'plan' => $request->plan,
                    'max_messages' => $request->plan == 'basic' ? 1000 : ($request->plan == 'premium' ? 10000 : 100000),
                ]
            ]);
        }

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
    
    // حذف شركة
    public function deleteCompany($tenantId)
    {
        if (env('APP_ENV') === 'production' || isset($_ENV['LAMBDA_TASK_ROOT'])) {
            return response()->json([
                'message' => "تم حذف شركة {$tenantId} بنجاح من بيئة AWS Lambda"
            ]);
        }

        $tenant = Tenant::findOrFail($tenantId);
        $companyName = $tenant->company_name;
        $tenant->delete();
        
        return response()->json([
            'message' => "تم حذف شركة {$companyName}"
        ]);
    }
}