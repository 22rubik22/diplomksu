<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProductController extends Controller
{
    /**
     * Проверка на дублирование товара
     * Возвращает true если товар существует (с учетом бренда)
     */
    private function checkDuplicate(array $data, $excludeProductId = null): ?Product
    {
        $query = Product::query();
        
        // Основные поля для проверки дубликата
        $query->where('title', $data['title'])
              ->where('brand_id', $data['brand_id']);
        
        if ($excludeProductId) {
            $query->where('id', '!=', $excludeProductId);
        }
        
        return $query->first();
    }
    
    /**
     * Получить список товаров с фильтрацией и пагинацией
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['categories', 'brand', 'primaryImage'])
            ->active();
        
        // 1. Фильтр по поиску (название, описание)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('brand', function($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                              ->orWhere('bio', 'like', "%{$search}%");
                    })
                    ->orWhereHas('categories', function($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            });
        }
        
        // 2. Фильтр по цене
        if ($request->has('price_from') && is_numeric($request->price_from)) {
            $query->where('price', '>=', $request->price_from);
        }
        
        if ($request->has('price_to') && is_numeric($request->price_to)) {
            $query->where('price', '<=', $request->price_to);
        }
        
        // 3. Фильтр по категории (поддерживает массив ID)
        if ($request->has('category_id') && !empty($request->category_id)) {
            $categoryIds = is_array($request->category_id) ? $request->category_id : [$request->category_id];
            $query->byCategories($categoryIds);
        }
        
        // 4. Фильтр по бренду
        if ($request->has('brand_id') && is_numeric($request->brand_id)) {
            $query->where('brand_id', $request->brand_id);
        }
        
        // 5. Фильтр по цветам
        if ($request->has('colors') && !empty($request->colors)) {
            $colors = is_array($request->colors) ? $request->colors : [$request->colors];
            $query->byColors($colors);
        }
        
        // 6. Фильтр по размерам
        if ($request->has('sizes') && !empty($request->sizes)) {
            $sizes = is_array($request->sizes) ? $request->sizes : [$request->sizes];
            $query->bySizes($sizes);
        }
        
        // 7. Фильтр по наличию
        if ($request->has('in_stock') && $request->boolean('in_stock')) {
            $query->inStock();
        }
        
        // 8. Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['price', 'title', 'created_at', 'publication_year', 'weight'];
        
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // 9. Пагинация
        $perPage = $request->get('per_page', 20);
        $perPage = min($perPage, 100);
        
        $products = $query->paginate($perPage);
        
        // Форматируем данные для ответа
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'price' => $product->price,
                'old_price' => $product->old_price,
                'discount_percent' => $product->discount_percent,
                'cover_image' => $product->cover_image,
                'is_in_stock' => $product->is_in_stock,
                'is_active' => $product->is_active,
                'color_list' => $product->color_list,
                'size_list' => $product->size_list,
                'quantity' => $product->quantity,
                'categories' => $product->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                }),
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                ] : null,
                'created_at' => $product->created_at,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
    
    /**
     * Получить детальную информацию о товаре
     */
    public function show(Product $product)
    {
        // Загружаем все необходимые связи
        $product->load([
            'categories',
            'brand',
            'images' => function($query) {
                $query->orderBy('sort_order');
            },
            'primaryImage',
            'reviews' => function($query) {
                $query->where('is_approved', true)->limit(10);
            },
            'reviews.user',
        ]);
        
        // Форматируем ответ
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'description' => $product->description,
                'publication_year' => $product->publication_year,
                'country' => $product->country,
                'consist' => $product->consist,
                'weight' => $product->weight,
                'price' => $product->price,
                'old_price' => $product->old_price,
                'discount_percent' => $product->discount_percent,
                'quantity' => $product->quantity,
                'is_in_stock' => $product->is_in_stock,
                'is_active' => $product->is_active,
                'cover_image' => $product->cover_image,
                'color' => $product->color,
                'size' => $product->size,
                'color_list' => $product->color_list,
                'size_list' => $product->size_list,
                'color_text' => $product->color_text,
                'size_text' => $product->size_text,
                'categories' => $product->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                }),
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                    'bio' => $product->brand->bio,
                ] : null,
                'images' => $product->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_path' => $image->image_path,
                        'is_primary' => $image->is_primary,
                        'sort_order' => $image->sort_order,
                    ];
                }),
                'reviews' => $product->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'title' => $review->title,
                        'comment' => $review->comment,
                        'user_name' => $review->user->name,
                        'created_at' => $review->created_at,
                    ];
                }),
                'average_rating' => $product->reviews->avg('rating'),
                'reviews_count' => $product->reviews->count(),
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]
        ]);
    }
    
    /**
     * Получить товар по slug
     */
    public function bySlug($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        return $this->show($product);
    }
    
    /**
     * Создать новый товар (только админ)
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
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'brand_id' => 'required|exists:brands,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'description' => 'nullable|string',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'country' => 'nullable|string|max:255',
            'consist' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'color' => 'nullable|array',
            'color.*' => 'string|max:50',
            'size' => 'nullable|array',
            'size.*' => 'string|max:50',
            'is_active' => 'nullable|boolean',
        ]);
        
        // Проверка на дублирование
        $duplicate = $this->checkDuplicate($validated);
        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'Товар с таким названием и брендом уже существует.',
                'duplicate_product_id' => $duplicate->id
            ], 409);
        }
        
        // Генерируем slug, если не указан
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Product::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }
        
        // Преобразуем массивы color и size в JSON
        if (isset($validated['color'])) {
            $validated['color'] = json_encode($validated['color']);
        }
        
        if (isset($validated['size'])) {
            $validated['size'] = json_encode($validated['size']);
        }
        
        // Устанавливаем значения по умолчанию
        $validated['is_active'] = $validated['is_active'] ?? true;
        
        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);
        
        $product = Product::create($validated);
        
        // Прикрепляем категории
        if (!empty($categoryIds)) {
            $product->categories()->sync($categoryIds);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Товар успешно создан',
            'data' => $product->load('categories', 'brand')
        ], 201);
    }
    
    /**
     * Обновить товар (только админ)
     */
    public function update(Request $request, Product $product)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($product->id)
            ],
            'brand_id' => 'sometimes|exists:brands,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'description' => 'nullable|string',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'country' => 'nullable|string|max:255',
            'consist' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'price' => 'sometimes|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'color' => 'nullable|array',
            'color.*' => 'string|max:50',
            'size' => 'nullable|array',
            'size.*' => 'string|max:50',
            'is_active' => 'nullable|boolean',
        ]);
        
        // Проверка на дублирование (исключая текущий товар)
        if (isset($validated['title']) && isset($validated['brand_id'])) {
            $duplicate = $this->checkDuplicate($validated, $product->id);
            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Товар с таким названием и брендом уже существует.',
                    'duplicate_product_id' => $duplicate->id
                ], 409);
            }
        }
        
        // Генерируем slug, если изменилось название
        if (isset($validated['title']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Product::where('slug', $validated['slug'])->where('id', '!=', $product->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }
        
        // Преобразуем массивы color и size в JSON
        if (isset($validated['color'])) {
            $validated['color'] = json_encode($validated['color']);
        }
        
        if (isset($validated['size'])) {
            $validated['size'] = json_encode($validated['size']);
        }
        
        $categoryIds = $validated['category_ids'] ?? null;
        unset($validated['category_ids']);
        
        $product->update($validated);
        
        // Обновляем категории, если они были переданы
        if ($categoryIds !== null) {
            $product->categories()->sync($categoryIds);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Товар успешно обновлен',
            'data' => $product->load('categories', 'brand')
        ]);
    }
    
    /**
     * Удалить товар (только админ)
     */
    public function destroy(Product $product)
    {
        // Проверка прав доступа
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        // Проверяем, есть ли товар в заказах
        if ($product->orderItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Невозможно удалить товар, так как он есть в заказах покупателей.'
            ], 400);
        }
        
        // Проверяем, есть ли товар в корзинах
        if ($product->cartItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Невозможно удалить товар, так как он есть в корзинах пользователей.'
            ], 400);
        }
        
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Товар успешно удален'
        ]);
    }
    
    /**
     * Переключить статус активности (только админ)
     */
    public function toggleActive(Product $product)
    {
        if (!auth()->user()?->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Только для администраторов.'
            ], 403);
        }
        
        $product->update(['is_active' => !$product->is_active]);
        
        return response()->json([
            'success' => true,
            'message' => 'Статус активности изменен',
            'is_active' => $product->is_active
        ]);
    }
    
    /**
     * Получить отзывы о товаре
     */
    public function reviews(Product $product)
    {
        $reviews = $product->reviews()
            ->where('is_approved', true)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'title' => $review->title,
                        'comment' => $review->comment,
                        'user_name' => $review->user->name,
                        'created_at' => $review->created_at,
                    ];
                }),
                'average_rating' => $product->reviews()->where('is_approved', true)->avg('rating'),
                'total_reviews' => $product->reviews()->where('is_approved', true)->count(),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ]
            ]
        ]);
    }
    
    /**
     * Получить похожие товары (по категориям и бренду)
     */
    public function similar(Product $product, Request $request)
    {
        $limit = $request->get('limit', 6);
        
        // Получаем ID категорий текущего товара
        $categoryIds = $product->categories->pluck('id')->toArray();
        
        $similar = Product::active()
            ->where('id', '!=', $product->id)
            ->where(function($query) use ($categoryIds, $product) {
                // Товары тех же категорий или того же бренда
                if (!empty($categoryIds)) {
                    $query->whereHas('categories', function($q) use ($categoryIds) {
                        $q->whereIn('categories.id', $categoryIds);
                    });
                }
                
                if ($product->brand_id) {
                    $query->orWhere('brand_id', $product->brand_id);
                }
            })
            ->with(['categories', 'brand', 'primaryImage'])
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'old_price' => $product->old_price,
                    'discount_percent' => $product->discount_percent,
                    'cover_image' => $product->cover_image,
                    'is_in_stock' => $product->is_in_stock,
                    'color_list' => $product->color_list,
                    'size_list' => $product->size_list,
                    'categories' => $product->categories->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                        ];
                    }),
                    'brand' => $product->brand ? [
                        'id' => $product->brand->id,
                        'name' => $product->brand->name,
                        'slug' => $product->brand->slug,
                    ] : null,
                ];
            });
            
        return response()->json([
            'success' => true,
            'data' => $similar
        ]);
    }
    
    /**
     * Экспорт списка товаров в Excel файл
     */
    public function exportExcel(Request $request)
    {
        // Получаем данные с фильтрацией (без пагинации)
        $query = Product::query()
            ->with(['categories', 'brand'])
            ->active();
        
        // 1. Фильтр по поиску (название, описание)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('brand', function($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                              ->orWhere('bio', 'like', "%{$search}%");
                    })
                    ->orWhereHas('categories', function($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            });
        }
        
        // 2. Фильтр по цене
        if ($request->has('price_from') && is_numeric($request->price_from)) {
            $query->where('price', '>=', $request->price_from);
        }
        
        if ($request->has('price_to') && is_numeric($request->price_to)) {
            $query->where('price', '<=', $request->price_to);
        }
        
        // 3. Фильтр по категории
        if ($request->has('category_id') && !empty($request->category_id)) {
            $categoryIds = is_array($request->category_id) ? $request->category_id : [$request->category_id];
            $query->byCategories($categoryIds);
        }
        
        // 4. Фильтр по бренду
        if ($request->has('brand_id') && is_numeric($request->brand_id)) {
            $query->where('brand_id', $request->brand_id);
        }
        
        // 5. Фильтр по наличию
        if ($request->has('in_stock') && $request->boolean('in_stock')) {
            $query->inStock();
        }
        
        // 6. Сортировка (для экспорта берем все записи)
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['price', 'title', 'created_at', 'publication_year', 'weight'];
        
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Получаем все товары (без пагинации для экспорта)
        $products = $query->get();
        
        // Создаем новый Excel документ
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Устанавливаем заголовок "Прайс-лист за (дата выгрузки)"
        $exportDate = now()->format('d.m.Y H:i:s');
        $sheet->setCellValue('A1', "Прайс-лист за {$exportDate}");
        $sheet->mergeCells('A1:O1');
        
        // Стиль для заголовка
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Заголовки колонок
        $headers = [
            'A' => 'ID',
            'B' => 'Название',
            'C' => 'Бренд',
            'D' => 'Категории',
            'E' => 'Цвета',
            'F' => 'Размеры',
            'G' => 'Страна производства',
            'H' => 'Состав',
            'I' => 'Год выпуска',
            'J' => 'Вес (г)',
            'K' => 'Цена',
            'L' => 'Старая цена',
            'M' => 'Скидка %',
            'N' => 'Количество',
            'O' => 'Статус'
        ];
        
        // Устанавливаем заголовки во второй строке
        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . '2', $header);
        }
        
        // Стиль для строки заголовков
        $headerStyle = $sheet->getStyle('A2:O2');
        $headerStyle->getFont()->setBold(true)->setSize(11);
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Добавляем данные
        $row = 3;
        foreach ($products as $product) {
            // Получаем список категорий
            $categoriesList = $product->categories->pluck('name')->implode(', ');
            
            // Получаем списки цветов и размеров
            $colorsList = $product->color_text ?: '—';
            $sizesList = $product->size_text ?: '—';
            
            $status = $product->is_in_stock ? 'В наличии' : 'Нет в наличии';
            
            $sheet->setCellValue('A' . $row, $product->id);
            $sheet->setCellValue('B' . $row, $product->title);
            $sheet->setCellValue('C' . $row, $product->brand?->name ?? '—');
            $sheet->setCellValue('D' . $row, $categoriesList ?: '—');
            $sheet->setCellValue('E' . $row, $colorsList);
            $sheet->setCellValue('F' . $row, $sizesList);
            $sheet->setCellValue('G' . $row, $product->country ?? '—');
            $sheet->setCellValue('H' . $row, $product->consist ?? '—');
            $sheet->setCellValue('I' . $row, $product->publication_year ?? '—');
            $sheet->setCellValue('J' . $row, $product->weight ?? '—');
            $sheet->setCellValue('K' . $row, $product->price);
            $sheet->setCellValue('L' . $row, $product->old_price ?? '—');
            $sheet->setCellValue('M' . $row, $product->discount_percent ? "{$product->discount_percent}%" : '0%');
            $sheet->setCellValue('N' . $row, $product->quantity);
            $sheet->setCellValue('O' . $row, $status);
            
            // Форматирование цен
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            
            // Если есть скидка - выделяем цену зеленым
            if ($product->discount_percent > 0) {
                $sheet->getStyle('K' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN));
            }
            
            // Если нет в наличии - выделяем красным
            if (!$product->is_in_stock) {
                $sheet->getStyle('N' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                $sheet->getStyle('O' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
            }
            
            $row++;
        }
        
        // Автоматическая ширина колонок
        foreach (range('A', 'O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Добавляем границы для всех ячеек с данными
        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            $sheet->getStyle('A2:O' . $lastRow)->applyFromArray($styleArray);
        }
        
        // Создаем файл для скачивания
        $fileName = 'price-list-' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        // Очищаем буфер вывода
        if (ob_get_length()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}