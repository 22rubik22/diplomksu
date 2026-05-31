<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address_line' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address_line' => $validated['address_line'] ?? null,
            'city' => $validated['city'] ?? null,
            'role' => 'user',
        ]);

        // Автоматически авторизуем после регистрации
        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Регистрация успешна',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ], 201);
    }

    /**
     * Авторизация пользователя
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);
    
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];
    
        $remember = $validated['remember'] ?? false;
    
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            return response()->json([
                'success' => true,
                'message' => 'Вход выполнен успешно',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address_line' => $user->address_line,
                    'city' => $user->city,
                    'role' => $user->role,
                    'avatar' => $user->avatar_url, // ДОБАВЛЕНО
                    'created_at' => $user->created_at,
                ]
            ]);
        }
    
        throw ValidationException::withMessages([
            'email' => ['Неверный email или пароль.'],
        ]);
    }
    

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json([
            'success' => true,
            'message' => 'Выход выполнен успешно'
        ]);
    }

    /**
     * Получение информации о текущем пользователе
     */
    public function me(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Не авторизован'
            ], 401);
        }
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address_line' => $user->address_line,
                'city' => $user->city,
                'role' => $user->role,
                'avatar' => $user->avatar_url, // ДОБАВЛЕНО
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Проверка авторизации
     */
    public function check(Request $request)
    {
        $user = Auth::user();
        
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::check() ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address_line' => $user->address_line,
                'city' => $user->city,
                'role' => $user->role,
                'avatar' => $user->avatar_url, // ДОБАВЛЕНО
                'created_at' => $user->created_at,
            ] : null
        ]);
    }
    
/**
     * Обновление профиля пользователя
     */
    public function updateProfile(Request $request)
{
    $user = Auth::user();
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Не авторизован'
        ], 401);
    }
    
    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'phone' => 'nullable|string|max:20',
        'address_line' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:255',
    ]);
    
    $user->update($validated);
    
    return response()->json([
        'success' => true,
        'message' => 'Профиль успешно обновлен',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address_line' => $user->address_line,
            'city' => $user->city,
            'role' => $user->role,
            'avatar' => $user->avatar_url, // ДОБАВЛЕНО
            'created_at' => $user->created_at,
        ]
    ]);
}
    
    /**
     * Смена пароля
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Не авторизован'
            ], 401);
        }
        
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        
        // Проверяем текущий пароль
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Текущий пароль неверен'
            ], 422);
        }
        
        // Обновляем пароль
        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Пароль успешно изменен'
        ]);
    }
    
      /**
     * Получение списка всех пользователей (только для админа/менеджера)
     */
  public function getUsersList(Request $request)
{
    $currentUser = Auth::user();
    
    // Проверяем права доступа
    if (!$currentUser || !($currentUser->isAdmin() || $currentUser->isManager())) {
        return response()->json([
            'success' => false,
            'message' => 'Доступ запрещен. Требуются права администратора или менеджера.'
        ], 403);
    }
    
    // Важно: загружаем связь bonus для корректной работы аксессора
    $query = User::query()->with('bonus');
    
    // Фильтрация по роли
    if ($request->has('role') && in_array($request->role, ['user', 'manager', 'admin'])) {
        $query->where('role', $request->role);
    }
    
    // Поиск по имени или email
    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
    
    // Сортировка
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');
    
    // Обработка особых полей для сортировки
    switch ($sortBy) {
        case 'orders_count':
            // Сортировка по количеству заказов
            $query->withCount('orders')
                  ->orderBy('orders_count', $sortOrder);
            break;
            
        case 'bonus':
            // Сортировка по бонусам через LEFT JOIN
            $query->leftJoin('bonus_user', 'users.id', '=', 'bonus_user.user_id')
                  ->select('users.*', \DB::raw('COALESCE(bonus_user.bonus, 0) as bonus_amount'))
                  ->orderBy('bonus_amount', $sortOrder);
            break;
            
        default:
            // Проверяем, существует ли колонка в таблице users
            if (in_array($sortBy, ['id', 'name', 'email', 'created_at', 'role', 'city', 'phone'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', $sortOrder);
            }
            break;
    }
    
    // Пагинация
    $perPage = $request->get('per_page', 15);
    $users = $query->paginate($perPage);
    
    // Трансформируем данные для ответа
    $users->getCollection()->transform(function($user) {
        // Получаем количество заказов (если не загружено через withCount)
        $ordersCount = $user->orders_count ?? $user->orders()->count();
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'city' => $user->city,
            'role' => $user->role,
            'avatar' => $user->avatar_url,
            'bonus' => $user->bonus, // Аксессор теперь работает, т.к. загружена связь 'bonus'
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'orders_count' => $ordersCount,
        ];
    });
    
    return response()->json([
        'success' => true,
        'data' => $users,
    ]);
}
    
   /**
 * Получение информации о конкретном пользователе (только для админа/менеджера)
 */
public function getUserById($id)
{
    $currentUser = Auth::user();
    
    // Проверяем права доступа
    if (!$currentUser || !($currentUser->isAdmin() || $currentUser->isManager())) {
        return response()->json([
            'success' => false,
            'message' => 'Доступ запрещен. Требуются права администратора или менеджера.'
        ], 403);
    }
    
    // Загружаем пользователя с подсчетом связанных записей и связью bonus
    $user = User::with('bonus')->withCount(['orders', 'reviews', 'favorites'])->find($id);
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Пользователь не найден'
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address_line' => $user->address_line,
            'city' => $user->city,
            'role' => $user->role,
            'yandex_id' => $user->yandex_id,
            'avatar' => $user->avatar_url,
            'bonus' => $user->bonus, // Аксессор работает, т.к. загружена связь 'bonus'
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'orders_count' => $user->orders_count,
            'reviews_count' => $user->reviews_count,
            'favorites_count' => $user->favorites_count,
        ]
    ]);
}

    
    /**
     * Назначение пользователя менеджером (только для администратора)
     */
    public function assignManager(Request $request, $id)
    {
        $currentUser = Auth::user();
        
        // Только администратор может назначать менеджеров
        if (!$currentUser || !$currentUser->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Требуются права администратора.'
            ], 403);
        }
        
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 404);
        }
        
        // Нельзя назначить менеджером самого себя (опционально)
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя изменить роль главного администратора'
            ], 422);
        }
        
        $newRole = $request->input('role', 'manager');
        
        // Валидация роли
        if (!in_array($newRole, ['manager', 'user', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Недопустимая роль'
            ], 422);
        }
        
        // Только администратор может назначить другого администратора
        if ($newRole === 'admin' && !$currentUser->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Только администратор может назначать других администраторов'
            ], 403);
        }
        
        $oldRole = $user->role;
        $user->role = $newRole;
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => "Роль пользователя изменена с '{$oldRole}' на '{$newRole}'",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }
    
    /**
     * Блокировка/разблокировка пользователя (мягкое удаление, нужно добавить soft deletes в модель)
     * Если используешь SoftDeletes
     */
    public function toggleUserBlock($id)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser || !$currentUser->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен. Требуются права администратора.'
            ], 403);
        }
        
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 404);
        }
        
        if ($user->trashed()) {
            $user->restore();
            $message = 'Пользователь успешно разблокирован';
        } else {
            $user->delete();
            $message = 'Пользователь успешно заблокирован';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'user_id' => $user->id,
            'is_blocked' => $user->trashed()
        ]);
    }
    
    /**
     * Обновление данных пользователя (администратором)
     */
    public function adminUpdateUser(Request $request, $id)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser || !($currentUser->isAdmin() || $currentUser->isManager())) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }
        
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не найден'
            ], 404);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'address_line' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'role' => 'sometimes|string|in:user,manager,admin',
        ]);
        
        // Только админ может менять роль
        if (isset($validated['role']) && !$currentUser->isAdmin()) {
            unset($validated['role']);
        }
        
        $user->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Данные пользователя обновлены',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'city' => $user->city,
                'role' => $user->role,
            ]
        ]);
    }

    /**
 * Обновление аватара пользователя
 */
public function updateAvatar(Request $request)
{
    $user = Auth::user();
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Не авторизован'
        ], 401);
    }
    
    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB max
    ]);
    
    // Удаляем старый аватар, если он не gravatar
    if ($user->avatar && !str_contains($user->avatar, 'gravatar.com')) {
        $oldPath = str_replace('/storage', 'public', $user->avatar);
        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }
    
    // Сохраняем новый аватар
    $path = $request->file('avatar')->store('avatars', 'public');
    $user->avatar = Storage::url($path);
    $user->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Аватар успешно обновлен',
        'avatar_url' => $user->avatar_url
    ]);
}

/**
 * Удаление аватара
 */
public function deleteAvatar(Request $request)
{
    $user = Auth::user();
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Не авторизован'
        ], 401);
    }
    
    // Удаляем файл, если это не gravatar
    if ($user->avatar && !str_contains($user->avatar, 'gravatar.com')) {
        $path = str_replace('/storage', 'public', $user->avatar);
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
    
    $user->avatar = null;
    $user->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Аватар удален',
        'avatar_url' => $user->avatar_url
    ]);
}
}