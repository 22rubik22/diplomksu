<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class OrderController extends Controller
{
    /**
     * Получить список заказов пользователя
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
        $status = $request->get('status');
        
        $query = $user->orders()->with('items');
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
    
    /**
     * Получить детальную информацию о заказе
     */
    public function show(Order $order)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        if ($order->user_id !== $user->id && !$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $order->load('items.product');
        
        // Форматируем items с дополнительной информацией
        $order->items->transform(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_title' => $item->product_title,
                'product_brand' => $item->product_brand,
                'color' => $item->color,
                'size' => $item->size,
                'color_label' => $item->color_label,
                'size_label' => $item->size_label,
                'full_title' => $item->full_title,
                'options_text' => $item->options_text,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total' => $item->total,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
    
    /**
     * Создать заказ
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Требуется авторизация'
            ], 401);
        }
        
        $validated = $request->validate([
            'delivery_method' => 'required|string|max:255',
            'delivery_address' => 'required|string',
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'payment_method' => 'required|string|max:255',
            'comment' => 'nullable|string',
            'use_bonus' => 'boolean',
            'items' => 'nullable|array',
        ]);
        
        $cart = Cart::where('user_id', $user->id)->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Корзина пуста'
            ], 400);
        }
        
        // Фильтруем только активные товары
        $activeCartItems = $cart->items->filter(function ($item) {
            return $item->product && $item->product->is_active && $item->product->is_in_stock;
        });
        
        if ($activeCartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступных товаров для оформления заказа'
            ], 400);
        }
        
        // Проверка наличия
        foreach ($activeCartItems as $item) {
            $product = $item->product;
            if ($product->quantity < $item->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Товара \"{$product->title}\" ({$item->color_label} {$item->size_label}) осталось только {$product->quantity} шт."
                ], 400);
            }
        }
        
        // Рассчитываем сумму
        $subtotal = $activeCartItems->sum(function ($item) {
            return $item->subtotal;
        });
        
        $deliveryPrice = $this->calculateDeliveryPrice($validated['delivery_method']);
        $totalAmount = $subtotal + $deliveryPrice;
        
        // Расчет использования бонусов (максимум 50% от суммы заказа)
        // Примечание: если у тебя нет системы бонусов, можно закомментировать эту логику
        $bonusUsed = 0;
        
        DB::beginTransaction();
        
        try {
            // Создаем заказ
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'status' => Order::STATUS_NEW,
                'total_amount' => $totalAmount,
                'delivery_method' => $validated['delivery_method'],
                'delivery_price' => $deliveryPrice,
                'delivery_address' => $validated['delivery_address'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'card' ? Order::PAYMENT_PAID : Order::PAYMENT_PENDING,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone ?? '',
                'comment' => $validated['comment'] ?? null,
            ]);
            
            // Создаем элементы заказа
            foreach ($activeCartItems as $item) {
                $product = $item->product;
                
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_title' => $product->title,
                    'product_brand' => $product->brand->name ?? null,
                    'color' => $item->color ?? null,
                    'size' => $item->size ?? null,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'total' => $item->subtotal,
                ]);
                
                // Уменьшаем количество товара
                $product->decrement('quantity', $item->quantity);
                
                // Удаляем из корзины
                $item->delete();
            }
            
            DB::commit();
            
            // Загружаем созданные items с форматированием
            $order->load('items');
            $order->items->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_title' => $item->product_title,
                    'product_brand' => $item->product_brand,
                    'color' => $item->color,
                    'size' => $item->size,
                    'color_label' => $item->color_label,
                    'size_label' => $item->size_label,
                    'full_title' => $item->full_title,
                    'options_text' => $item->options_text,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                ];
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно создан',
                'data' => [
                    'order' => $order,
                    'bonus_used' => $bonusUsed,
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании заказа',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Отменить заказ
     */
    public function cancel(Order $order)
    {
        $user = auth()->user();
        
        if (!$user || $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        if (!in_array($order->status, [Order::STATUS_NEW, Order::STATUS_PENDING])) {
            return response()->json([
                'success' => false,
                'message' => 'Невозможно отменить заказ в текущем статусе'
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            // Возвращаем товары
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $item->product->increment('quantity', $item->quantity);
                }
            }
            
            $order->update(['status' => Order::STATUS_CANCELLED]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Заказ отменен'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отмене заказа'
            ], 500);
        }
    }
    
    /**
     * Рассчитать стоимость доставки
     */
    private function calculateDeliveryPrice($deliveryMethod)
    {
        $deliveryPrices = [
            'pickup' => 0,
            'courier' => 300,
            'post' => 250,
        ];
        
        return $deliveryPrices[$deliveryMethod] ?? 0;
    }
    
    // ============= АДМИНСКИЕ МЕТОДЫ =============
    
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
        $status = $request->get('status');
        
        $query = Order::with('user', 'items');
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
    
    public function updateStatus(Request $request, Order $order)
    {
        $user = auth()->user();
        
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [
                Order::STATUS_NEW,
                Order::STATUS_PENDING,
                Order::STATUS_PROCESSING,
                Order::STATUS_SHIPPED,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
                Order::STATUS_REFUNDED,
            ]),
            'admin_comment' => 'nullable|string',
        ]);
        
        $order->update([
            'status' => $validated['status'],
            'admin_comment' => $validated['admin_comment'] ?? $order->admin_comment,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Статус заказа обновлен',
            'data' => $order
        ]);
    }
    
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $user = auth()->user();
        
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $validated = $request->validate([
            'payment_status' => 'required|in:' . implode(',', [
                Order::PAYMENT_PENDING,
                Order::PAYMENT_PAID,
                Order::PAYMENT_FAILED,
                Order::PAYMENT_REFUNDED,
            ]),
        ]);
        
        $order->update(['payment_status' => $validated['payment_status']]);
        
        return response()->json([
            'success' => true,
            'message' => 'Статус оплаты обновлен',
            'data' => $order
        ]);
    }
    
    /**
     * Экспорт заказов в Excel (для администратора)
     */
    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $query = Order::with('user', 'items');
        
        // Фильтр по статусу
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Фильтр по статусу оплаты
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Поиск
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }
        
        // Фильтр по дате
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortFields = ['created_at', 'total_amount', 'order_number', 'status'];
        
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Получаем все заказы для экспорта
        $orders = $query->get();
        
        // Создаем Excel документ
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Заголовок
        $exportDate = now()->format('d.m.Y H:i:s');
        $sheet->setCellValue('A1', "Отчет по заказам за {$exportDate}");
        $sheet->mergeCells('A1:R1');
        
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Заголовки колонок
        $headers = [
            'A' => 'ID',
            'B' => 'Номер заказа',
            'C' => 'Дата создания',
            'D' => 'Статус',
            'E' => 'Статус оплаты',
            'F' => 'Клиент',
            'G' => 'Email',
            'H' => 'Телефон',
            'I' => 'Сумма заказа',
            'J' => 'Доставка',
            'K' => 'Стоимость доставки',
            'L' => 'Адрес доставки',
            'M' => 'Дата доставки',
            'N' => 'Способ оплаты',
            'O' => 'Количество товаров',
            'P' => 'Цвета/Размеры',
            'Q' => 'Товары',
            'R' => 'Комментарий'
        ];
        
        // Устанавливаем заголовки во второй строке
        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . '2', $header);
        }
        
        // Стиль для заголовков
        $headerStyle = $sheet->getStyle('A2:R2');
        $headerStyle->getFont()->setBold(true)->setSize(11);
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Данные
        $row = 3;
        foreach ($orders as $order) {
            // Формируем список товаров с опциями
            $itemsList = [];
            $optionsList = [];
            foreach ($order->items as $item) {
                $itemText = $item->product_title;
                if ($item->color || $item->size) {
                    $itemText .= ' (';
                    if ($item->color) $itemText .= $item->color_label;
                    if ($item->color && $item->size) $itemText .= ', ';
                    if ($item->size) $itemText .= $item->size_label;
                    $itemText .= ')';
                }
                $itemsList[] = $itemText . ' x' . $item->quantity;
                $optionsList[] = ($item->color ? $item->color_label : '—') . ' / ' . ($item->size ? $item->size_label : '—');
            }
            
            $sheet->setCellValue('A' . $row, $order->id);
            $sheet->setCellValue('B' . $row, $order->order_number);
            $sheet->setCellValue('C' . $row, $order->created_at->format('d.m.Y H:i:s'));
            $sheet->setCellValue('D' . $row, $this->getOrderStatusLabel($order->status));
            $sheet->setCellValue('E' . $row, $this->getPaymentStatusLabel($order->payment_status));
            $sheet->setCellValue('F' . $row, $order->customer_name);
            $sheet->setCellValue('G' . $row, $order->customer_email);
            $sheet->setCellValue('H' . $row, $order->customer_phone);
            $sheet->setCellValue('I' . $row, $order->total_amount);
            $sheet->setCellValue('J' . $row, $this->getDeliveryMethodLabel($order->delivery_method));
            $sheet->setCellValue('K' . $row, $order->delivery_price);
            $sheet->setCellValue('L' . $row, $order->delivery_address);
            $sheet->setCellValue('M' . $row, $order->delivery_date ? date('d.m.Y', strtotime($order->delivery_date)) : '—');
            $sheet->setCellValue('N' . $row, $this->getPaymentMethodLabel($order->payment_method));
            $sheet->setCellValue('O' . $row, $order->items->sum('quantity'));
            $sheet->setCellValue('P' . $row, implode('; ', $optionsList));
            $sheet->setCellValue('Q' . $row, implode('; ', $itemsList));
            $sheet->setCellValue('R' . $row, $order->comment ?? '—');
            
            // Форматирование сумм
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            
            // Цветовая индикация статусов
            $this->applyStatusColor($sheet, 'D' . $row, $order->status);
            $this->applyPaymentStatusColor($sheet, 'E' . $row, $order->payment_status);
            
            $row++;
        }
        
        // Автоматическая ширина колонок
        foreach (range('A', 'R') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Добавляем границы
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
            $sheet->getStyle('A2:R' . $lastRow)->applyFromArray($styleArray);
        }
        
        // Фиксируем первую строку
        $sheet->freezePane('A3');
        
        // Создаем файл
        $fileName = 'orders-report-' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
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
    
    /**
     * Экспорт детального отчета по заказам (с товарами)
     */
    public function exportDetailedExcel(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $query = Order::with('user', 'items.product');
        
        // Фильтры (те же, что и в основном экспорте)
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Заголовок
        $exportDate = now()->format('d.m.Y H:i:s');
        $sheet->setCellValue('A1', "Детальный отчет по заказам за {$exportDate}");
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Заголовки для детального отчета
        $headers = [
            'A' => 'ID заказа',
            'B' => 'Номер заказа',
            'C' => 'Дата',
            'D' => 'Статус',
            'E' => 'Клиент',
            'F' => 'ID товара',
            'G' => 'Название товара',
            'H' => 'Бренд',
            'I' => 'Цвет',
            'J' => 'Размер',
            'K' => 'Цена',
            'L' => 'Количество',
            'M' => 'Сумма',
            'N' => 'Статус оплаты'
        ];
        
        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . '2', $header);
        }
        
        // Стиль заголовков
        $headerStyle = $sheet->getStyle('A2:N2');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Данные (каждый товар в отдельной строке)
        $row = 3;
        foreach ($orders as $order) {
            if ($order->items->isEmpty()) {
                // Если нет товаров, все равно показываем заказ
                $sheet->setCellValue('A' . $row, $order->id);
                $sheet->setCellValue('B' . $row, $order->order_number);
                $sheet->setCellValue('C' . $row, $order->created_at->format('d.m.Y H:i:s'));
                $sheet->setCellValue('D' . $row, $this->getOrderStatusLabel($order->status));
                $sheet->setCellValue('E' . $row, $order->customer_name);
                $sheet->setCellValue('F' . $row, '—');
                $sheet->setCellValue('G' . $row, '—');
                $sheet->setCellValue('H' . $row, '—');
                $sheet->setCellValue('I' . $row, '—');
                $sheet->setCellValue('J' . $row, '—');
                $sheet->setCellValue('K' . $row, '—');
                $sheet->setCellValue('L' . $row, '—');
                $sheet->setCellValue('M' . $row, '—');
                $sheet->setCellValue('N' . $row, $this->getPaymentStatusLabel($order->payment_status));
                $row++;
            } else {
                foreach ($order->items as $item) {
                    $sheet->setCellValue('A' . $row, $order->id);
                    $sheet->setCellValue('B' . $row, $order->order_number);
                    $sheet->setCellValue('C' . $row, $order->created_at->format('d.m.Y H:i:s'));
                    $sheet->setCellValue('D' . $row, $this->getOrderStatusLabel($order->status));
                    $sheet->setCellValue('E' . $row, $order->customer_name);
                    $sheet->setCellValue('F' . $row, $item->product_id ?? '—');
                    $sheet->setCellValue('G' . $row, $item->product_title);
                    $sheet->setCellValue('H' . $row, $item->product_brand ?? '—');
                    $sheet->setCellValue('I' . $row, $item->color_label ?? '—');
                    $sheet->setCellValue('J' . $row, $item->size_label ?? '—');
                    $sheet->setCellValue('K' . $row, $item->price);
                    $sheet->setCellValue('L' . $row, $item->quantity);
                    $sheet->setCellValue('M' . $row, $item->total);
                    $sheet->setCellValue('N' . $row, $this->getPaymentStatusLabel($order->payment_status));
                    
                    $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                    
                    $row++;
                }
            }
        }
        
        // Автоширина
        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Границы
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
            $sheet->getStyle('A2:N' . $lastRow)->applyFromArray($styleArray);
        }
        
        $sheet->freezePane('A3');
        
        $fileName = 'orders-detailed-' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        if (ob_get_length()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Получить текстовую метку статуса заказа
     */
    private function getOrderStatusLabel($status)
    {
        $labels = [
            Order::STATUS_NEW => 'Новый',
            Order::STATUS_PENDING => 'Ожидает',
            Order::STATUS_PROCESSING => 'В обработке',
            Order::STATUS_SHIPPED => 'Отправлен',
            Order::STATUS_DELIVERED => 'Доставлен',
            Order::STATUS_CANCELLED => 'Отменен',
            Order::STATUS_REFUNDED => 'Возврат',
        ];
        
        return $labels[$status] ?? $status;
    }
    
    /**
     * Получить текстовую метку статуса оплаты
     */
    private function getPaymentStatusLabel($status)
    {
        $labels = [
            Order::PAYMENT_PENDING => 'Ожидает оплаты',
            Order::PAYMENT_PAID => 'Оплачен',
            Order::PAYMENT_FAILED => 'Ошибка оплаты',
            Order::PAYMENT_REFUNDED => 'Возврат',
        ];
        
        return $labels[$status] ?? $status;
    }
    
    /**
     * Получить текстовую метку способа доставки
     */
    private function getDeliveryMethodLabel($method)
    {
        $labels = [
            'pickup' => 'Самовывоз',
            'courier' => 'Курьер',
            'post' => 'Почта',
        ];
        
        return $labels[$method] ?? $method;
    }
    
    /**
     * Получить текстовую метку способа оплаты
     */
    private function getPaymentMethodLabel($method)
    {
        $labels = [
            'card' => 'Банковская карта',
            'cash' => 'Наличные',
            'online' => 'Онлайн оплата',
        ];
        
        return $labels[$method] ?? $method;
    }
    
    /**
     * Применить цвет для статуса заказа
     */
    private function applyStatusColor($sheet, $cell, $status)
    {
        $colors = [
            Order::STATUS_NEW => 'FF3498DB',      // Синий
            Order::STATUS_PENDING => 'FFF39C12',   // Оранжевый
            Order::STATUS_PROCESSING => 'FF9B59B6', // Фиолетовый
            Order::STATUS_SHIPPED => 'FF2ECC71',   // Зеленый
            Order::STATUS_DELIVERED => 'FF27AE60', // Темно-зеленый
            Order::STATUS_CANCELLED => 'FFE74C3C', // Красный
            Order::STATUS_REFUNDED => 'FFE67E22',  // Оранжевый
        ];
        
        if (isset($colors[$status])) {
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB($colors[$status]);
        }
    }
    
    /**
     * Применить цвет для статуса оплаты
     */
    private function applyPaymentStatusColor($sheet, $cell, $status)
    {
        $colors = [
            Order::PAYMENT_PENDING => 'FFF39C12',  // Оранжевый
            Order::PAYMENT_PAID => 'FF2ECC71',     // Зеленый
            Order::PAYMENT_FAILED => 'FFE74C3C',   // Красный
            Order::PAYMENT_REFUNDED => 'FFE67E22', // Оранжевый
        ];
        
        if (isset($colors[$status])) {
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB($colors[$status]);
        }
    }
}