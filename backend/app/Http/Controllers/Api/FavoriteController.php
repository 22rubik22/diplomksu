<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Получить список избранных товаров пользователя
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        $perPage = $request->get('per_page', 20);
        $perPage = min($perPage, 100);
        
        $favorites = $user->favorites()
            ->with(['categories', 'primaryImage', 'brand'])
            ->paginate($perPage);
        
        $favorites->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'price' => $product->price,
                'old_price' => $product->old_price,
                'discount_percent' => $product->discount_percent,
                'cover_image' => $product->cover_image,
                'is_in_stock' => $product->is_in_stock,
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                ] : null,
                'categories' => $product->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                }),
                'created_at' => $product->pivot->created_at,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    /**
     * Добавить товар в избранное
     */
    public function add(Product $product)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        // Проверяем, не добавлен ли уже товар
        if ($user->favorites()->where('product_id', $product->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Товар уже в избранном'
            ], 400);
        }
        
        $user->favorites()->attach($product->id);
        
        // Возвращаем обновлённый список IDs
        $ids = $user->favorites()->pluck('product_id');
        
        return response()->json([
            'success' => true,
            'message' => 'Товар добавлен в избранное',
            'data' => [
                'product_id' => $product->id,
                'title' => $product->title,
                'favorite_ids' => $ids
            ]
        ]);
    }

    /**
     * Удалить товар из избранного
     */
    public function remove(Product $product)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        $deleted = $user->favorites()->detach($product->id);
        
        if ($deleted === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден в избранном'
            ], 404);
        }
        
        // Возвращаем обновлённый список IDs
        $ids = $user->favorites()->pluck('product_id');
        
        return response()->json([
            'success' => true,
            'message' => 'Товар удален из избранного',
            'data' => [
                'product_id' => $product->id,
                'favorite_ids' => $ids
            ]
        ]);
    }

    /**
     * Проверить, находится ли товар в избранном
     */
    public function check(Product $product)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => true,
                'is_favorite' => false
            ]);
        }
        
        $isFavorite = $user->favorites()->where('product_id', $product->id)->exists();
        
        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite
        ]);
    }

    /**
     * Получить ID всех избранных товаров (для синхронизации на фронте)
     */
    public function ids()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        $ids = $user->favorites()->pluck('product_id');
        
        return response()->json([
            'success' => true,
            'data' => $ids
        ]);
    }
}