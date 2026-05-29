<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Получить или создать корзину для текущего пользователя/сессии
     */
    private function getCart()
    {
        $user = auth()->user();
        
        if ($user) {
            // Авторизованный пользователь
            return Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => null]
            );
        } else {
            // Гость - используем session_id из cookie
            $sessionId = request()->cookie('cart_session_id');
            
            if (!$sessionId) {
                $sessionId = Str::uuid()->toString();
            }
            
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId],
                ['user_id' => null]
            );
            
            // Устанавливаем cookie на 30 дней
            Cookie::queue('cart_session_id', $sessionId, 60 * 24 * 30);
            
            return $cart;
        }
    }
    
    /**
     * Получить содержимое корзины
     */
    public function index()
    {
        $cart = $this->getCart();
        
        $cart->load(['items.product' => function($query) {
            $query->with('primaryImage');
        }]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cart->id,
                'items' => $cart->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'color' => $item->color,
                        'size' => $item->size,
                        'color_label' => $item->color_label,
                        'size_label' => $item->size_label,
                        'full_title' => $item->full_title,
                        'options_text' => $item->options_text,
                        'has_options' => $item->has_options,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'title' => $item->product->title,
                            'slug' => $item->product->slug,
                            'price' => $item->product->price,
                            'cover_image' => $item->product->cover_image,
                            'is_in_stock' => $item->product->is_in_stock,
                            'is_active' => (bool) $item->product->is_active,
                            'quantity' => $item->product->quantity,
                            'color_list' => $item->product->color_list,
                            'size_list' => $item->product->size_list,
                        ] : null,
                    ];
                }),
                'total' => $cart->total,
                'items_count' => $cart->items_count,
            ]
        ]);
    }
    
    /**
     * Добавить товар в корзину
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:99',
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
        ]);
        
        $product = Product::findOrFail($validated['product_id']);
        
        // Проверка активности товара
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Этот товар временно недоступен для заказа'
            ], 400);
        }
        
        // Проверка наличия товара
        if (!$product->is_in_stock) {
            return response()->json([
                'success' => false,
                'message' => 'Товар отсутствует на складе'
            ], 400);
        }
        
        if ($product->quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => "Доступно только {$product->quantity} шт."
            ], 400);
        }
        
        // Проверка, что выбранный цвет доступен для товара
        if (isset($validated['color']) && !empty($product->color_list)) {
            if (!in_array($validated['color'], $product->color_list)) {
                return response()->json([
                    'success' => false,
                    'message' => "Цвет \"{$validated['color']}\" не доступен для этого товара"
                ], 400);
            }
        }
        
        // Проверка, что выбранный размер доступен для товара
        if (isset($validated['size']) && !empty($product->size_list)) {
            if (!in_array($validated['size'], $product->size_list)) {
                return response()->json([
                    'success' => false,
                    'message' => "Размер \"{$validated['size']}\" не доступен для этого товара"
                ], 400);
            }
        }
        
        $cart = $this->getCart();
        
        // Проверяем, есть ли уже такой товар с такими же опциями в корзине
        $cartItem = $cart->items()
            ->where('product_id', (int)$product->id)
            ->where('color', $validated['color'] ?? null)
            ->where('size', $validated['size'] ?? null)
            ->first();
        
        if ($cartItem) {
            // Товар с такими же опциями уже есть — обновляем количество
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            if ($product->quantity < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Всего доступно {$product->quantity} шт. В корзине уже {$cartItem->quantity} шт."
                ], 400);
            }
            $cartItem->update([
                'quantity' => $newQuantity,
                'price' => $product->price,
            ]);
        } else {
            // Товара с такими опциями нет — создаём новый
            $cartItem = $cart->items()->create([
                'product_id' => (int)$product->id,
                'color' => $validated['color'] ?? null,
                'size' => $validated['size'] ?? null,
                'quantity' => $validated['quantity'],
                'price' => $product->price,
            ]);
        }
        
        // Загружаем обновлённую корзину
        $cart->load(['items.product' => function($query) {
            $query->with('primaryImage');
        }]);
        
        return response()->json([
            'success' => true,
            'message' => 'Товар добавлен в корзину',
            'data' => [
                'cart_item_id' => $cartItem->id,
                'product_title' => $product->title,
                'color' => $cartItem->color,
                'size' => $cartItem->size,
                'quantity' => $cartItem->quantity,
                'items' => $cart->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'color' => $item->color,
                        'size' => $item->size,
                        'color_label' => $item->color_label,
                        'size_label' => $item->size_label,
                        'full_title' => $item->full_title,
                        'options_text' => $item->options_text,
                        'has_options' => $item->has_options,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'title' => $item->product->title,
                            'slug' => $item->product->slug,
                            'price' => $item->product->price,
                            'cover_image' => $item->product->cover_image,
                            'is_in_stock' => $item->product->is_in_stock,
                            'is_active' => (bool) $item->product->is_active,
                            'quantity' => $item->product->quantity,
                            'color_list' => $item->product->color_list,
                            'size_list' => $item->product->size_list,
                        ] : null,
                    ];
                }),
                'total' => $cart->total,
                'items_count' => $cart->items_count,
            ]
        ]);
    }
    
    /**
     * Обновить количество товара в корзине
     */
    public function update(Request $request, CartItem $cartItem)
    {
        // Проверяем, что товар принадлежит корзине текущего пользователя
        $cart = $this->getCart();
        
        if ($cartItem->cart_id != $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден в вашей корзине'
            ], 404);
        }
        
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0|max:99',
        ]);
        
        $product = $cartItem->product;
        
        if ($validated['quantity'] == 0) {
            // Удаляем товар
            $cartItem->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Товар удален из корзины'
            ]);
        }
        
        // Проверка активности товара
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Этот товар временно недоступен для заказа'
            ], 400);
        }
        
        // Проверка наличия
        if ($product->quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => "Доступно только {$product->quantity} шт."
            ], 400);
        }
        
        // Обновляем цену на случай изменения
        $cartItem->update([
            'quantity' => $validated['quantity'],
            'price' => $product->price,
        ]);
        
        // Получаем обновленную информацию о корзине
        $cart->load(['items.product' => function($query) {
            $query->with('primaryImage');
        }]);
        
        return response()->json([
            'success' => true,
            'message' => 'Количество обновлено',
            'data' => [
                'quantity' => $cartItem->quantity,
                'subtotal' => $cartItem->subtotal,
                'total' => $cart->total,
                'items_count' => $cart->items_count,
            ]
        ]);
    }
    
    /**
     * Обновить опции товара в корзине (цвет/размер)
     */
    public function updateOptions(Request $request, CartItem $cartItem)
    {
        // Проверяем, что товар принадлежит корзине текущего пользователя
        $cart = $this->getCart();
        
        if ($cartItem->cart_id != $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден в вашей корзине'
            ], 404);
        }
        
        $validated = $request->validate([
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
        ]);
        
        $product = $cartItem->product;
        
        // Проверка, что выбранный цвет доступен для товара
        if (isset($validated['color']) && !empty($product->color_list)) {
            if (!in_array($validated['color'], $product->color_list)) {
                return response()->json([
                    'success' => false,
                    'message' => "Цвет \"{$validated['color']}\" не доступен для этого товара"
                ], 400);
            }
        }
        
        // Проверка, что выбранный размер доступен для товара
        if (isset($validated['size']) && !empty($product->size_list)) {
            if (!in_array($validated['size'], $product->size_list)) {
                return response()->json([
                    'success' => false,
                    'message' => "Размер \"{$validated['size']}\" не доступен для этого товара"
                ], 400);
            }
        }
        
        // Проверяем, нет ли уже такого же товара с новыми опциями в корзине
        $existingItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('color', $validated['color'] ?? null)
            ->where('size', $validated['size'] ?? null)
            ->where('id', '!=', $cartItem->id)
            ->first();
        
        if ($existingItem) {
            // Если есть, объединяем с существующим товаром
            $existingItem->update([
                'quantity' => $existingItem->quantity + $cartItem->quantity,
                'price' => $product->price,
            ]);
            $cartItem->delete();
            
            $message = 'Опции товара обновлены и объединены с существующим товаром в корзине';
        } else {
            // Обновляем опции текущего товара
            $cartItem->update([
                'color' => $validated['color'] ?? null,
                'size' => $validated['size'] ?? null,
                'price' => $product->price,
            ]);
            
            $message = 'Опции товара обновлены';
        }
        
        // Загружаем обновленную корзину
        $cart->load(['items.product' => function($query) {
            $query->with('primaryImage');
        }]);
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'items' => $cart->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'color' => $item->color,
                        'size' => $item->size,
                        'color_label' => $item->color_label,
                        'size_label' => $item->size_label,
                        'full_title' => $item->full_title,
                        'options_text' => $item->options_text,
                        'has_options' => $item->has_options,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'title' => $item->product->title,
                            'slug' => $item->product->slug,
                            'price' => $item->product->price,
                            'cover_image' => $item->product->cover_image,
                            'is_in_stock' => $item->product->is_in_stock,
                            'is_active' => (bool) $item->product->is_active,
                            'quantity' => $item->product->quantity,
                        ] : null,
                    ];
                }),
                'total' => $cart->total,
                'items_count' => $cart->items_count,
            ]
        ]);
    }
    
    /**
     * Удалить товар из корзины
     */
    public function remove(CartItem $cartItem)
    {
        $cart = $this->getCart();
        
        if ($cartItem->cart_id != $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден в вашей корзине'
            ], 404);
        }
        
        $cartItem->delete();
        
        // Загружаем обновленную корзину
        $cart->load(['items.product' => function($query) {
            $query->with('primaryImage');
        }]);
        
        return response()->json([
            'success' => true,
            'message' => 'Товар удален из корзины',
            'data' => [
                'total' => $cart->total,
                'items_count' => $cart->items_count,
            ]
        ]);
    }
    
    /**
     * Очистить всю корзину
     */
    public function clear()
    {
        $cart = $this->getCart();
        $cart->items()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Корзина очищена'
        ]);
    }
    
    /**
     * Слить корзину гостя с корзиной пользователя (после авторизации)
     */
    public function merge(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        $sessionId = $request->cookie('cart_session_id');
        
        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Гостевая корзина не найдена'
            ], 404);
        }
        
        $guestCart = Cart::where('session_id', $sessionId)->first();
        
        if (!$guestCart || $guestCart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Гостевая корзина пуста'
            ], 404);
        }
        
        $userCart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['session_id' => null]
        );
        
        // Переносим товары из гостевой корзины в корзину пользователя
        foreach ($guestCart->items as $guestItem) {
            $product = $guestItem->product;
            
            // Проверяем активность товара перед переносом
            if (!$product->is_active) {
                continue; // Пропускаем неактивные товары
            }
            
            // Ищем товар с такими же опциями в корзине пользователя
            $existingItem = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->where('color', $guestItem->color)
                ->where('size', $guestItem->size)
                ->first();
            
            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $guestItem->quantity,
                    'price' => $guestItem->price,
                ]);
            } else {
                $userCart->items()->create([
                    'product_id' => $guestItem->product_id,
                    'color' => $guestItem->color,
                    'size' => $guestItem->size,
                    'quantity' => $guestItem->quantity,
                    'price' => $guestItem->price,
                ]);
            }
        }
        
        // Удаляем гостевую корзину
        $guestCart->delete();
        
        // Удаляем cookie
        Cookie::queue(Cookie::forget('cart_session_id'));
        
        // Загружаем обновленную корзину
        $userCart->load(['items.product' => function($query) {
            $query->with('primaryImage');
        }]);
        
        return response()->json([
            'success' => true,
            'message' => 'Корзина успешно объединена',
            'data' => [
                'total' => $userCart->total,
                'items_count' => $userCart->items_count,
            ]
        ]);
    }
    
    /**
     * Получить количество товаров в корзине (для иконки)
     */
    public function count()
    {
        $cart = $this->getCart();
        
        return response()->json([
            'success' => true,
            'data' => [
                'count' => $cart->items_count,
                'total' => $cart->total,
            ]
        ]);
    }
}