<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SubscriptionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant();
        
        // التحقق من صلاحية الاشتراك
        if ($tenant && $tenant->subscription_ends_at && now()->greaterThan($tenant->subscription_ends_at)) {
            return response()->json([
                'error' => 'انتهت صلاحية الاشتراك. يرجى تجديد الاشتراك للاستمرار في استخدام النظام.',
                'code' => 'SUBSCRIPTION_EXPIRED'
            ], 403);
        }
        
        return $next($request);
    }
}
