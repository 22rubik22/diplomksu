<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Получить список всех брендов с количеством товаров
     */
    public function index(Request $request)
    {
        $query = Brand::query();
        
        // Добавляем подсчет количества товаров для каждого бренда
        $query->withCount('products');
        
        // Поиск по имени
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Сортировка
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Обработка сортировки по количеству товаров
        if ($sortBy === 'products_count') {
            $query->orderBy('products_count', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        $brands = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }
    
    /**
     * Получить один бренд с его товарами
     */
    public function show(Brand $brand)
    {
        // Загружаем товары бренда с категориями
        $brand->load(['products' => function($query) {
            $query->with(['categories', 'primaryImage'])->latest();
        }]);
        
        // Формируем данные товаров вручную, включая имя бренда
        $productsData = [];
        foreach ($brand->products as $product) {
            $productsData[] = [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'price' => $product->price,
                'old_price' => $product->old_price,
                'cover_image' => $product->cover_image,
                'is_in_stock' => $product->is_in_stock,
                'is_active' => $product->is_active,
                'description' => $product->description,
                'publication_year' => $product->publication_year,
                'country' => $product->country,
                'consist' => $product->consist,
                'weight' => $product->weight,
                'quantity' => $product->quantity,
                'discount_percent' => $product->discount_percent,
                'categories' => $product->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug
                    ];
                }),
                // Добавляем данные о бренде для каждого товара
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug
                ],
                'brand_name' => $brand->name // Для простоты
            ];
        }
        
        // Создаем массив с данными бренда
        $brandData = [
            'id' => $brand->id,
            'name' => $brand->name,
            'slug' => $brand->slug,
            'desc' => $brand->desc,
            'photo' => $brand->photo,
            'created_at' => $brand->created_at,
            'updated_at' => $brand->updated_at,
            'products_count' => count($productsData),
            'products' => $productsData
        ];
        
        return response()->json([
            'success' => true,
            'data' => $brandData
        ]);
    }
    
    /**
     * Получить товары бренда
     */
    public function products(Brand $brand)
    {
        // Загружаем товары с категориями
        $products = $brand->products()
            ->with(['categories', 'primaryImage'])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'old_price' => $product->old_price,
                    'cover_image' => $product->cover_image,
                    'is_in_stock' => $product->is_in_stock,
                    'categories' => $product->categories->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                        ];
                    }),
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
    
    /**
     * Создать новый бренд (только админ)
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
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'desc' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Генерируем slug, если не указан
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Проверяем уникальность slug
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Brand::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }
        
        // Обработка загрузки фото
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('brands', 'public');
            $validated['photo'] = Storage::url($photoPath);
        }
        
        $brand = Brand::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Бренд успешно создан',
            'data' => $brand
        ], 201);
    }
    
    /**
     * Обновить бренд (только админ)
     */
    public function update(Request $request, Brand $brand)
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
                Rule::unique('brands', 'slug')->ignore($brand->id)
            ],
            'desc' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Генерируем slug, если изменилось имя
        if (isset($validated['name']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Проверяем уникальность slug
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Brand::where('slug', $validated['slug'])->where('id', '!=', $brand->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }
        
        // Обработка загрузки фото
        if ($request->hasFile('photo')) {
            // Удаляем старое фото, если оно есть
            if ($brand->photo) {
                $oldPath = str_replace('/storage', 'public', $brand->photo);
                Storage::delete($oldPath);
            }
            
            $photoPath = $request->file('photo')->store('brands', 'public');
            $validated['photo'] = Storage::url($photoPath);
        }
        
        $brand->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Бренд успешно обновлен',
            'data' => $brand
        ]);
    }
    
    /**
     * Удалить бренд (только админ)
     */
    public function destroy(Brand $brand)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        // Проверяем, есть ли товары у этого бренда
        if ($brand->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Невозможно удалить бренд, так как есть товары, привязанные к нему.'
            ], 400);
        }
        
        // Удаляем фото бренда
        if ($brand->photo) {
            $oldPath = str_replace('/storage', 'public', $brand->photo);
            Storage::delete($oldPath);
        }
        
        $brand->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Бренд успешно удален'
        ]);
    }
}