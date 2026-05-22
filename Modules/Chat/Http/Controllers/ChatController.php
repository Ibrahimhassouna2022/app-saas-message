<?php

namespace Modules\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Message;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;

class ChatController extends Controller
{
    public function index()
    {
        $plan = tenant('plan');
        $messageLimit = $this->getMessageLimit($plan);
        
        return response()->json([
            'tenant_id' => tenant('id'),
            'company_name' => tenant('company_name'),
            'plan' => $plan,
            'message_limit' => $messageLimit,
            'current_messages' => Message::count(),
        ]);
    }
    
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'body' => 'required|string',
        ]);

        $plan = tenant('plan');
        $currentCount = Message::count();
        $limit = $this->getMessageLimit($plan);
        
        // التحقق من الاشتراك والحد الأقصى للرسائل
        if ($currentCount >= $limit) {
            return response()->json([
                'error' => "تم الوصول للحد الأقصى للرسائل في خطة {$plan} (الحد: {$limit} رسالة)"
            ], 403);
        }
        
        // التأكد من وجود المحادثة أو إنشاؤها تلقائياً للتجربة
        $conversationId = $request->conversation_id;
        Conversation::firstOrCreate(
            ['id' => $conversationId], 
            [
                'title' => 'محادثة تجريبية', 
                'created_by' => 1,
                'participants' => json_encode([1]) // إضافة المشارك رقم 1
            ]
        );

        // التأكد من وجود مستخدم تجريبي برقم 1 لتجنب خطأ Foreign Key
        $senderId = $request->sender_id ?? 1;
        \App\Models\User::firstOrCreate(
            ['id' => $senderId],
            [
                'name' => 'مستخدم تجريبي',
                'email' => 'test@company.com',
                'password' => bcrypt('password')
            ]
        );

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $request->body,
            'is_read' => false,
        ]);
        
        return response()->json($message, 201);
    }
    
    private function getMessageLimit($plan)
    {
        return match($plan) {
            'basic' => 1000,
            'premium' => 10000,
            'enterprise' => 100000,
            default => 500,
        };
    }
    
    public function getConversations()
    {
        return response()->json(Conversation::all());
    }
    
    public function getMessages($conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)->get();
        return response()->json($messages);
    }
}
