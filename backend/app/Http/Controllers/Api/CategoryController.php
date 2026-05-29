<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Получить список всех категорий
     */
    public function index(Request $request)
    {
        $query = Category::with('parent')->orderBy('sort_order');
        
        // Фильтр по активности
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }
        
        // Фильтр по родительской категории
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }
        
        // Фильтр по поиску (название или slug)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        $categories = $query->get();
        
        // Добавляем URL изображения к каждой категории
        $categories->transform(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
                'image_url' => $category->image_url,
                'parent_id' => $category->parent_id,
                'parent' => $category->parent,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    /**
     * Получить дерево категорий (иерархическую структуру)
     */
    public function tree()
    {
        $categories = Category::with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
        
        // Рекурсивно добавляем URL изображений
        $categories->transform(function ($category) {
            return $this->formatCategoryWithChildren($category);
        });
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    /**
     * Рекурсивно форматирует категорию с детьми
     */
    private function formatCategoryWithChildren($category)
    {
        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image' => $category->image,
            'image_url' => $category->image_url,
            'parent_id' => $category->parent_id,
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];
        
        if ($category->children && $category->children->count() > 0) {
            $data['children'] = $category->children->map(function ($child) {
                return $this->formatCategoryWithChildren($child);
            });
        }
        
        return $data;
    }
    
    /**
     * Получить одну категорию
     */
    public function show(Category $category)
    {
        $category->load('parent', 'children');
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
                'image_url' => $category->image_url,
                'parent_id' => $category->parent_id,
                'parent' => $category->parent,
                'children' => $category->children,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ]
        ]);
    }
    
    /**
     * Создать новую категорию (только админ)
     */
    public function store(Request $request)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        
        // Генерируем slug, если не указан
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Проверяем уникальность slug
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }
        
        // Обработка загрузки изображения
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $validated['image'] = $imagePath;
        }
        
        // Устанавливаем значения по умолчанию
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;
        
        $category = Category::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Категория успешно создана',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
                'image_url' => $category->image_url,
                'parent_id' => $category->parent_id,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
            ]
        ], 201);
    }
    
    /**
     * Обновить категорию (только админ)
     */
    public function update(Request $request, Category $category)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($category->id)
            ],
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'delete_image' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        
        // Генерируем slug, если изменилось имя
        if (isset($validated['name']) && (empty($validated['slug']) || $request->input('slug') === '')) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Проверяем уникальность slug
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }
        
        // Обработка удаления изображения
        if ($request->boolean('delete_image')) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = null;
        }
        
        // Обработка загрузки нового изображения
        if ($request->hasFile('image')) {
            // Удаляем старое изображение, если оно есть
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            
            $imagePath = $request->file('image')->store('categories', 'public');
            $validated['image'] = $imagePath;
        }
        
        // Убираем delete_image из validated, так как его нет в fillable
        unset($validated['delete_image']);
        
        $category->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Категория успешно обновлена',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
                'image_url' => $category->image_url,
                'parent_id' => $category->parent_id,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
            ]
        ]);
    }
    
    /**
     * Удалить категорию (только админ)
     */
    public function destroy(Category $category)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        // Проверяем, есть ли товары в этой категории
        if ($category->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Невозможно удалить категорию, так как есть товары, привязанные к ней.'
            ], 400);
        }
        
        // Удаляем изображение
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        
        // Переназначаем детей (если есть) на родителя
        if ($category->children()->exists()) {
            $parentId = $category->parent_id;
            Category::where('parent_id', $category->id)->update(['parent_id' => $parentId]);
        }
        
        $category->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Категория успешно удалена'
        ]);
    }
    
    /**
     * Удалить изображение категории (только админ)
     */
    public function deleteImage(Category $category)
    {
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
            $category->update(['image' => null]);
            
            return response()->json([
                'success' => true,
                'message' => 'Изображение удалено'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Изображение не найдено'
        ], 404);
    }
    
    /**
     * Массовое изменение статуса активности (только админ)
     */
    public function toggleActive(Request $request, Category $category)
    {
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        $category->update(['is_active' => !$category->is_active]);
        
        return response()->json([
            'success' => true,
            'message' => 'Статус категории изменен',
            'is_active' => $category->is_active
        ]);
    }
}