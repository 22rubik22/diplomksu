<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductImageController extends Controller
{
    /**
     * Получить все изображения товара
     */
    public function index(Product $product)
    {
        $images = $product->images()->orderBy('sort_order')->get();
        
        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    }
    
    /**
     * Получить основное изображение товара
     */
    public function primary(Product $product)
    {
        $primary = $product->primaryImage;
        
        return response()->json([
            'success' => true,
            'data' => $primary
        ]);
    }
    
    /**
     * Загрузить новое изображение (только админ)
     */
    public function store(Request $request, Product $product)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        // Загружаем изображение
        $path = $request->file('image')->store('products', 'public');
        
        // Если это основное изображение, снимаем флаг с других
        if (isset($validated['is_primary']) && $validated['is_primary']) {
            $product->images()->update(['is_primary' => false]);
        }
        
        $image = $product->images()->create([
            'image_path' => '/storage/' . $path,
            'is_primary' => $validated['is_primary'] ?? false,
            'sort_order' => $validated['sort_order'] ?? $product->images()->count(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Изображение успешно загружено',
            'data' => $image
        ], 201);
    }
    
    /**
     * Обновить информацию об изображении (только админ)
     */
    public function update(Request $request, ProductImage $image)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        $validated = $request->validate([
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        
        // Если делаем основным, снимаем флаг с других изображений товара
        if (isset($validated['is_primary']) && $validated['is_primary']) {
            $image->product->images()->where('id', '!=', $image->id)->update(['is_primary' => false]);
        }
        
        $image->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Изображение обновлено',
            'data' => $image
        ]);
    }
    
    /**
     * Удалить изображение (только админ)
     */
    public function destroy(ProductImage $image)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        $product = $image->product;
        $wasPrimary = $image->is_primary;
        
        // Удаляем файл из хранилища
        $path = str_replace('/storage/', '', $image->image_path);
        Storage::disk('public')->delete($path);
        
        $image->delete();
        
        // Если удалили основное изображение, назначаем новое основное
        if ($wasPrimary) {
            $newPrimary = $product->images()->orderBy('sort_order')->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Изображение успешно удалено'
        ]);
    }
    
    /**
     * Массовое обновление порядка сортировки (только админ)
     */
    public function reorder(Request $request, Product $product)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        $validated = $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:product_images,id',
            'images.*.sort_order' => 'required|integer|min:0',
        ]);
        
        foreach ($validated['images'] as $item) {
            ProductImage::where('id', $item['id'])
                ->where('product_id', $product->id)
                ->update(['sort_order' => $item['sort_order']]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Порядок сортировки обновлен'
        ]);
    }
    
    /**
     * Установить изображение как основное (только админ)
     */
    public function setPrimary(ProductImage $image)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        // Снимаем флаг со всех изображений товара
        $image->product->images()->update(['is_primary' => false]);
        
        // Устанавливаем новое основное
        $image->update(['is_primary' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Основное изображение установлено',
            'data' => $image
        ]);
    }
}