<?php
// app/Http/Controllers/Api/PaymentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Создание платежа (симуляция)
     */
    public function createPayment(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'return_url' => 'required|url',
            'description' => 'nullable|string'
        ]);
        
        // Симуляция успешного создания платежа
        $paymentId = 'payment_' . uniqid() . '_' . time();
        
        // Формируем URL для возврата с параметрами успеха
        $returnUrl = $validated['return_url'];
        $separator = strpos($returnUrl, '?') === false ? '?' : '&';
        $confirmationUrl = $returnUrl . $separator . 'payment_status=success&payment_id=' . $paymentId;
        
        Log::info('Payment created (simulation)', [
            'user_id' => $user->id,
            'payment_id' => $paymentId,
            'amount' => $validated['amount']
        ]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $paymentId,
                'confirmation_url' => $confirmationUrl
            ]
        ]);
    }
    
    /**
     * Проверка статуса платежа
     */
    public function checkPayment(Request $request)
    {
        $paymentId = $request->input('payment_id');
        
        if (!$paymentId) {
            return response()->json([
                'success' => false,
                'message' => 'payment_id обязателен'
            ], 400);
        }
        
        // Всегда возвращаем успешный статус для симуляции
        return response()->json([
            'success' => true,
            'data' => [
                'status' => 'succeeded',
                'paid' => true,
                'amount' => $request->input('amount', 0)
            ]
        ]);
    }
    
    /**
     * Webhook для уведомлений
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Payment webhook received: ', $payload);
        
        return response()->json(['success' => true]);
    }
}