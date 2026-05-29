<?php
// app/Http/Controllers/Api/YandexAuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YandexAuthController extends Controller
{
    /**
     * Получить URL для редиректа на Яндекс
     */
    public function redirectToYandex()
{
    $params = [
        'client_id' => config('services.yandex.client_id'),
        'redirect_uri' => config('services.yandex.redirect'),
        'response_type' => 'code',
    ];
    
    $url = 'https://oauth.yandex.ru/authorize?' . http_build_query($params);
    
    return redirect()->away($url);
}
    
    /**
     * Обработка callback от Яндекса
     */
    public function handleYandexCallback()
    {
        $code = request()->get('code');
        $state = request()->get('state');
        
        if (!$code) {
            return redirect()->to('http://ivybook.ru/login')->withErrors(['error' => 'Ошибка авторизации через Яндекс.']);
        }
        
        try {
            // Получаем токен
            $tokenResponse = $this->getAccessToken($code);
            
            if (!isset($tokenResponse['access_token'])) {
                return redirect()->to('http://ivybook.ru/login')->withErrors(['error' => 'Не удалось получить токен.']);
            }
            
            // Получаем данные пользователя
            $userData = $this->getUserData($tokenResponse['access_token']);
            
            if (!$userData) {
                return redirect()->to('http://ivybook.ru/login')->withErrors(['error' => 'Не удалось получить данные пользователя.']);
            }
            
            // Находим или создаем пользователя
            $user = $this->findOrCreateUser($userData);
            
            // Авторизуем
            Auth::login($user, true);
            request()->session()->regenerate();
            
            return redirect()->to('http://ivybook.ru/')->with('success', 'Добро пожаловать!');
            
        } catch (\Exception $e) {
            Log::error('Yandex auth error: ' . $e->getMessage());
            return redirect()->to('http://ivybook.ru/login')->withErrors(['error' => 'Ошибка при авторизации.']);
        }
    }
    
    /**
     * Получение токена доступа
     */
    private function getAccessToken($code)
    {
        $clientId = config('services.yandex.client_id');
        $clientSecret = config('services.yandex.client_secret');
        $redirectUri = config('services.yandex.redirect');
        
        $response = Http::asForm()->post('https://oauth.yandex.ru/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
        ]);
        
        return $response->json();
    }
    
    /**
     * Получение данных пользователя
     */
    private function getUserData($accessToken)
    {
        $response = Http::withHeaders([
            'Authorization' => 'OAuth ' . $accessToken,
        ])->get('https://login.yandex.ru/info?format=json');
        
        if (!$response->successful()) {
            Log::error('Yandex user info failed: ' . $response->body());
            return null;
        }
        
        $data = $response->json();
        
        return [
            'yandex_id' => $data['id'] ?? null,
            'name' => $data['real_name'] ?? $data['display_name'] ?? $data['login'] ?? null,
            'email' => $data['default_email'] ?? null,
            'avatar' => $data['default_avatar_id'] ?? null,
            'login' => $data['login'] ?? null,
        ];
    }
    
    /**
     * Найти или создать пользователя
     */
    private function findOrCreateUser($userData)
    {
        // Если есть email, ищем по email
        if (!empty($userData['email'])) {
            $user = User::where('email', $userData['email'])->first();
            if ($user) {
                // Обновляем yandex_id если его нет
                if (!$user->yandex_id && $userData['yandex_id']) {
                    $user->yandex_id = $userData['yandex_id'];
                    $user->save();
                }
                return $user;
            }
        }
        
        // Ищем по yandex_id
        if (!empty($userData['yandex_id'])) {
            $user = User::where('yandex_id', $userData['yandex_id'])->first();
            if ($user) {
                return $user;
            }
        }
        
        // Создаем нового пользователя
        $name = $userData['name'] ?? $userData['login'] ?? 'Пользователь Яндекс';
        
        // Формируем email если его нет
        $email = $userData['email'] ?? $userData['login'] . '@yandex.ru';
        
        // Проверяем уникальность email
        $originalEmail = $email;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = $originalEmail . '_' . $counter;
            $counter++;
        }
        
        $avatar = $userData['avatar'] 
            ? 'https://avatars.yandex.net/get-yapic/' . $userData['avatar'] . '/islands-200'
            : null;
        
        return User::create([
            'name' => $name,
            'email' => $email,
            'yandex_id' => $userData['yandex_id'],
            'avatar' => $avatar,
            'password' => bcrypt(Str::random(32)),
            'email_verified_at' => now(),
            'role' => 'user',
        ]);
    }
    
    /**
     * Получить URL для входа через Яндекс (для фронтенда)
     */
    public function getYandexAuthUrl()
    {
        $clientId = config('services.yandex.client_id');
        $redirectUri = config('services.yandex.redirect');
        
        $params = [
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => csrf_token(),
        ];
        
        $authUrl = 'https://oauth.yandex.ru/authorization?' . http_build_query($params);
        
        return response()->json([
            'success' => true,
            'auth_url' => $authUrl
        ]);
    }
}