<?php
// app/Http/Controllers/Api/PaymentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use YooKassa\Client;

class PaymentController extends Controller
{
    private $client;
    
    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuth('1355501', 'test_J9zrDkWzsEp48q-aqThhMiZMcIffvJ5jOOkdOoRALRI');
    }
    
    /**
     * Создание платежа
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
        
        try {
            $idempotenceKey = uniqid('', true);
            
            // Создаём платеж в ЮKassa
            $payment = $this->client->createPayment(
                [
                    'amount' => [
                        'value' => (string)number_format($validated['amount'], 2, '.', ''),
                        'currency' => 'RUB',
                    ],
                    'confirmation' => [
                        'type' => 'redirect',
                        'return_url' => $validated['return_url'],
                    ],
                    'capture' => true,
                    'description' => $validated['description'] ?? 'Оплата заказа в магазине',
                    'metadata' => [
                        'user_id' => $user->id,
                    ]
                ],
                $idempotenceKey
            );
            
            // Исправлено: getConfirmationUrl() вместо getUrl()
            $confirmation = $payment->getConfirmation();
            $confirmationUrl = $confirmation->getConfirmationUrl();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $payment->getId(),
                    'confirmation_url' => $confirmationUrl
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('ЮKassa payment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании платежа: ' . $e->getMessage()
            ], 500);
        }
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
        
        try {
            $payment = $this->client->getPaymentInfo($paymentId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $payment->getStatus(),
                    'paid' => $payment->getPaid(),
                    'amount' => $payment->getAmount()->getValue()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Check payment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Webhook для уведомлений от ЮKassa
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('ЮKassa webhook: ', $payload);
        
        // Проверяем, что это уведомление об успешной оплате
        if (isset($payload['object']['status']) && $payload['object']['status'] === 'succeeded') {
            $paymentId = $payload['object']['id'];
            Log::info('Payment succeeded: ' . $paymentId);
            
            // Здесь можно создать заказ или обновить его статус
            // Данные о заказе можно получить из metadata
            // $metadata = $payload['object']['metadata'] ?? [];
        }
        
        return response()->json(['success' => true]);
    }
}