<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Получить отзывы к товару (только одобренные)
     */
    public function index(Product $product, Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $perPage = min($perPage, 50);
        
        $reviews = $product->reviews()
            ->approved()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        $reviews->getCollection()->transform(function ($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'user_name' => $review->user->name,
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'average_rating' => $product->reviews()->approved()->avg('rating'),
                'total_reviews' => $product->reviews()->approved()->count(),
                'rating_distribution' => $this->getRatingDistribution($product),
            ]
        ]);
    }
    
    /**
     * Проверить возможность оставить отзыв
     */
    public function reviewPermission(Product $product)
    {
        $user = auth()->user();
        
        // Если не авторизован
        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => [
                    'is_logged_in' => false,
                    'can_review' => false,
                    'reason' => 'Требуется авторизация',
                    'user_review' => null,
                    'has_purchased' => false
                ]
            ]);
        }
        
        // Проверяем, покупал ли пользователь этот товар
        $hasPurchased = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('items', function($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->exists();
        
        // Проверяем, есть ли уже отзыв
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();
        
        $canReview = $hasPurchased && !$existingReview;
        $canEdit = $existingReview && !$existingReview->is_approved;
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_logged_in' => true,
                'can_review' => $canReview,
                'can_edit' => $canEdit,
                'reason' => !$hasPurchased ? 'Товар не куплен' : ($existingReview ? 'Уже есть отзыв' : null),
                'has_purchased' => $hasPurchased,
                'user_review' => $existingReview ? [
                    'id' => $existingReview->id,
                    'rating' => $existingReview->rating,
                    'title' => $existingReview->title,
                    'comment' => $existingReview->comment,
                    'is_approved' => $existingReview->is_approved,
                    'created_at' => $existingReview->created_at,
                ] : null
            ]
        ]);
    }

    /**
     * Получить отзыв текущего пользователя на товар
     */
    public function userReview(Product $product)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        $review = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();
        
        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }
    
    /**
     * Создать отзыв (только после покупки товара)
     */
    public function store(Request $request, Product $product)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        // Проверяем, покупал ли пользователь этот товар
        $hasPurchased = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('items', function($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->exists();
        
        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'Вы можете оставить отзыв только на купленный товар'
            ], 403);
        }
        
        // Проверяем, не оставлял ли пользователь уже отзыв
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();
        
        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Вы уже оставили отзыв на этот товар'
            ], 400);
        }
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:5000',
        ]);
        
        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'is_approved' => false, // Требуется модерация
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Отзыв отправлен на модерацию',
            'data' => $review
        ], 201);
    }
    
    /**
     * Обновить свой отзыв
     */
    public function update(Request $request, Review $review)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        // Проверяем, что отзыв принадлежит пользователю
        if ($review->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        // Нельзя редактировать одобренный отзыв
        if ($review->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Одобренный отзыв нельзя редактировать'
            ], 400);
        }
        
        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:5000',
        ]);
        
        $review->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Отзыв обновлен',
            'data' => $review
        ]);
    }
    
    /**
     * Удалить свой отзыв
     */
    public function destroy(Review $review)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        // Проверяем, что отзыв принадлежит пользователю ИЛИ пользователь админ/менеджер
        if ($review->user_id !== $user->id && !$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $review->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Отзыв удален'
        ]);
    }
    
    /**
     * Получить распределение оценок по товару
     */
    private function getRatingDistribution(Product $product)
    {
        $distribution = [
            1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0
        ];
        
        $ratings = $product->reviews()
            ->approved()
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->get();
        
        foreach ($ratings as $rating) {
            $distribution[$rating->rating] = $rating->count;
        }
        
        return $distribution;
    }
    
    // ============= АДМИНСКИЕ МЕТОДЫ =============
    
    /**
     * Получить все отзывы (только админ/менеджер)
     */
    public function adminIndex(Request $request)
    {
        $user = auth()->user();
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $perPage = $request->get('per_page', 20);
        $status = $request->get('status'); // approved, pending
        
        // Загружаем product с брендом
        $query = Review::with(['user', 'product.brand']);
        
        if ($status === 'approved') {
            $query->where('is_approved', true);
        } elseif ($status === 'pending') {
            $query->where('is_approved', false);
        }
        
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        $reviews = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }
    
    /**
     * Одобрить отзыв (только админ/менеджер)
     */
    public function approve(Review $review)
    {
        $user = auth()->user();
        
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $review->update(['is_approved' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Отзыв одобрен',
            'data' => $review
        ]);
    }
    
    /**
     * Отклонить отзыв (только админ/менеджер)
     */
    public function reject(Review $review)
    {
        $user = auth()->user();
        
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $review->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Отзыв отклонен и удален'
        ]);
    }
}