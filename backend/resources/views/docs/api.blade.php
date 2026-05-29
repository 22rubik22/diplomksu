<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - IvyBook</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        
        header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .method {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 10px;
        }
        
        .method-get { background: #61affe; color: white; }
        .method-post { background: #49cc90; color: white; }
        .method-put { background: #fca130; color: white; }
        .method-patch { background: #50e3c2; color: white; }
        .method-delete { background: #f93e3e; color: white; }
        
        .endpoint {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .endpoint h3 {
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .url {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            border-left: 4px solid #667eea;
        }
        
        .description {
            margin: 15px 0;
            color: #666;
        }
        
        .parameters, .response {
            margin-top: 20px;
        }
        
        .parameters h4, .response h4 {
            margin-bottom: 10px;
            color: #667eea;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: #f8f9fa;
            border-radius: 6px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background: #e9ecef;
            font-weight: 600;
        }
        
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            margin-top: 10px;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: normal;
            margin-left: 10px;
        }
        
        .badge-auth {
            background: #ffc107;
            color: #000;
        }
        
        .badge-public {
            background: #28a745;
            color: white;
        }
        
        .badge-admin {
            background: #dc3545;
            color: white;
        }
        
        footer {
            text-align: center;
            padding: 30px;
            color: #666;
            margin-top: 40px;
            border-top: 1px solid #ddd;
        }
        
        .sidebar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 250px;
        }
        
        .sidebar a {
            display: block;
            color: #667eea;
            text-decoration: none;
            padding: 5px 0;
            font-size: 14px;
        }
        
        .sidebar a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: static;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="container">
                <h1>📚 IvyBook API Documentation</h1>
                <p>RESTful API для интернет-магазина книг</p>
                <p>Base URL: <code>https://ivybook.ru/api</code></p>
            </div>
        </header>
        
        <div class="sidebar">
            <h4>Содержание</h4>
            <a href="#auth">🔐 Аутентификация</a>
            <a href="#genres">📚 Жанры</a>
            <a href="#authors">✍️ Авторы</a>
            <a href="#books">📖 Книги</a>
            <a href="#book-images">🖼️ Изображения книг</a>
            <a href="#cart">🛒 Корзина</a>
            <a href="#favorites">❤️ Избранное</a>
            <a href="#orders">📦 Заказы</a>
            <a href="#reviews">⭐ Отзывы</a>
            <a href="#events">🎪 Мероприятия</a>
            <a href="#bookcrossing">📖 Буккроссинг</a>
        </div>
        
        <div class="content">
            <section id="auth">
                <h2>🔐 Аутентификация</h2>
                <p>Система использует сессии Laravel. После успешного входа сервер устанавливает cookie <code>laravel_session</code>.</p>
            </section>
            
            <!-- Регистрация -->
            <div class="endpoint" id="register">
                <h3>
                    <span class="method method-post">POST</span>
                    /api/register
                    <span class="badge badge-public">Публичный</span>
                </h3>
                <div class="description">
                    Регистрация нового пользователя
                </div>
                <div class="url">
                    POST https://ivybook.ru/api/register
                </div>
                
                <div class="parameters">
                    <h4>📥 Параметры запроса (JSON)</h4>
                    <table>
                        <thead>
                            <tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>name</td><td>string</td><td>✅</td><td>Имя пользователя</td></tr>
                            <tr><td>email</td><td>string</td><td>✅</td><td>Email адрес</td></tr>
                            <tr><td>password</td><td>string</td><td>✅</td><td>Пароль (мин. 8 символов)</td></tr>
                            <tr><td>password_confirmation</td><td>string</td><td>✅</td><td>Подтверждение пароля</td></tr>
                            <tr><td>phone</td><td>string</td><td>❌</td><td>Номер телефона</td></tr>
                            <tr><td>city</td><td>string</td><td>❌</td><td>Город</td></tr>
                            <tr><td>address_line</td><td>string</td><td>❌</td><td>Адрес</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (201 Created)</h4>
                    <div class="code-block">
{
    "success": true,
    "message": "Регистрация успешна",
    "user": {
        "id": 1,
        "name": "Иван Петров",
        "email": "ivan@example.com",
        "role": "user"
    }
}
                    </div>
                </div>
            </div>
            
            <!-- Вход -->
            <div class="endpoint" id="login">
                <h3>
                    <span class="method method-post">POST</span>
                    /api/login
                    <span class="badge badge-public">Публичный</span>
                </h3>
                <div class="description">
                    Авторизация пользователя
                </div>
                <div class="url">
                    POST https://ivybook.ru/api/login
                </div>
                
                <div class="parameters">
                    <h4>📥 Параметры запроса (JSON)</h4>
                    <table>
                        <thead>
                            <tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>email</td><td>string</td><td>✅</td><td>Email пользователя</td></tr>
                            <tr><td>password</td><td>string</td><td>✅</td><td>Пароль</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (200 OK)</h4>
                    <div class="code-block">
{
    "success": true,
    "message": "Вход выполнен успешно",
    "user": {
        "id": 1,
        "name": "Иван Петров",
        "email": "ivan@example.com",
        "role": "user"
    }
}
                    </div>
                </div>
            </div>
            
            <!-- Профиль -->
            <div class="endpoint" id="me">
                <h3>
                    <span class="method method-get">GET</span>
                    /api/me
                    <span class="badge badge-auth">Требует авторизацию</span>
                </h3>
                <div class="description">
                    Получение информации о текущем пользователе
                </div>
                <div class="url">
                    GET https://ivybook.ru/api/me
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (200 OK)</h4>
                    <div class="code-block">
{
    "success": true,
    "user": {
        "id": 1,
        "name": "Иван Петров",
        "email": "ivan@example.com",
        "phone": "+7 999 123-45-67",
        "address_line": "ул. Тверская, д. 1",
        "city": "Москва",
        "role": "user",
        "created_at": "2024-01-01T12:00:00.000000Z"
    }
}
                    </div>
                </div>
            </div>
            
            <!-- Выход -->
            <div class="endpoint" id="logout">
                <h3>
                    <span class="method method-post">POST</span>
                    /api/logout
                    <span class="badge badge-auth">Требует авторизацию</span>
                </h3>
                <div class="description">
                    Выход из системы
                </div>
                <div class="url">
                    POST https://ivybook.ru/api/logout
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (200 OK)</h4>
                    <div class="code-block">
{
    "success": true,
    "message": "Выход выполнен успешно"
}
                    </div>
                </div>
            </div>
            
            <!-- Проверка авторизации -->
            <div class="endpoint" id="check">
                <h3>
                    <span class="method method-get">GET</span>
                    /api/check
                    <span class="badge badge-public">Публичный</span>
                </h3>
                <div class="description">
                    Проверка статуса авторизации
                </div>
                <div class="url">
                    GET https://ivybook.ru/api/check
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (200 OK)</h4>
                    <div class="code-block">
{
    "authenticated": true,
    "user": {
        "id": 1,
        "name": "Иван Петров",
        "email": "ivan@example.com",
        "role": "user"
    }
}
                    </div>
                </div>
            </div>
            
            <!-- Жанры -->
            <section id="genres">
                <h2>📚 Управление жанрами</h2>
                <p>Методы для управления жанрами книг. Создание, редактирование и удаление доступны только администраторам.</p>
            </section>
            
            <!-- Список жанров -->
            <div class="endpoint" id="genres-list">
                <h3>
                    <span class="method method-get">GET</span>
                    /api/genres
                    <span class="badge badge-public">Публичный</span>
                </h3>
                <div class="description">
                    Получение списка всех жанров
                </div>
                <div class="url">
                    GET https://ivybook.ru/api/genres
                </div>
                
                <div class="parameters">
                    <h4>📥 Параметры запроса (Query)</h4>
                    <table>
                        <thead>
                            <tr><th>Параметр</th><th>Тип</th><th>Описание</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>active</td><td>boolean</td><td>Фильтр по активности (true/false)</td></tr>
                            <tr><td>parent_id</td><td>integer</td><td>Фильтр по родительской категории</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (200 OK)</h4>
                    <div class="code-block">
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Фантастика",
            "slug": "fantastika",
            "description": "Книги в жанре научной фантастики",
            "parent_id": null,
            "image": null,
            "sort_order": 1,
            "is_active": true,
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:00:00.000000Z",
            "parent": null
        }
    ]
}
                    </div>
                </div>
            </div>
            
            <!-- Дерево жанров -->
            <div class="endpoint" id="genres-tree">
                <h3>
                    <span class="method method-get">GET</span>
                    /api/genres/tree
                    <span class="badge badge-public">Публичный</span>
                </h3>
                <div class="description">
                    Получение иерархического дерева жанров
                </div>
                <div class="url">
                    GET https://ivybook.ru/api/genres/tree
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (200 OK)</h4>
                    <div class="code-block">
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Художественная литература",
            "children": [
                {
                    "id": 2,
                    "name": "Фантастика",
                    "children": []
                },
                {
                    "id": 3,
                    "name": "Детектив",
                    "children": []
                }
            ]
        }
    ]
}
                    </div>
                </div>
            </div>
            
            <!-- Просмотр жанра -->
            <div class="endpoint" id="genres-show">
                <h3>
                    <span class="method method-get">GET</span>
                    /api/genres/{id}
                    <span class="badge badge-public">Публичный</span>
                </h3>
                <div class="description">
                    Получение информации о конкретном жанре
                </div>
                <div class="url">
                    GET https://ivybook.ru/api/genres/1
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (200 OK)</h4>
                    <div class="code-block">
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Фантастика",
        "slug": "fantastika",
        "description": "Книги в жанре научной фантастики",
        "parent_id": null,
        "image": null,
        "sort_order": 1,
        "is_active": true,
        "parent": null,
        "children": []
    }
}
                    </div>
                </div>
            </div>
            
            <!-- Создание жанра -->
            <div class="endpoint" id="genres-create">
                <h3>
                    <span class="method method-post">POST</span>
                    /api/genres
                    <span class="badge badge-admin">Только админ</span>
                </h3>
                <div class="description">
                    Создание нового жанра (требуется авторизация администратора)
                </div>
                <div class="url">
                    POST https://ivybook.ru/api/genres
                </div>
                
                <div class="parameters">
                    <h4>📥 Параметры запроса (JSON)</h4>
                    <table>
                        <thead>
                            <tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>name</td><td>string</td><td>✅</td><td>Название жанра</td></tr>
                            <tr><td>slug</td><td>string</td><td>❌</td><td>URL-идентификатор (генерируется автоматически)</td></tr>
                            <tr><td>description</td><td>string</td><td>❌</td><td>Описание жанра</td></tr>
                            <tr><td>parent_id</td><td>integer</td><td>❌</td><td>ID родительского жанра</td></tr>
                            <tr><td>image</td><td>string</td><td>❌</td><td>Путь к изображению</td></tr>
                            <tr><td>sort_order</td><td>integer</td><td>❌</td><td>Порядок сортировки (по умолчанию 0)</td></tr>
                            <tr><td>is_active</td><td>boolean</td><td>❌</td><td>Активен ли жанр (по умолчанию true)</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (201 Created)</h4>
                    <div class="code-block">
{
    "success": true,
    "message": "Жанр успешно создан",
    "data": {
        "id": 5,
        "name": "Поэзия",
        "slug": "poeziya",
        "description": "Стихотворения и поэмы",
        "sort_order": 0,
        "is_active": true,
        "updated_at": "2024-01-01T12:00:00.000000Z",
        "created_at": "2024-01-01T12:00:00.000000Z"
    }
}
                    </div>
                </div>
            </div>
            
            <!-- Обновление жанра -->
            <div class="endpoint" id="genres-update">
                <h3>
                    <span class="method method-put">PUT</span>
                    /api/genres/{id}
                    <span class="badge badge-admin">Только админ</span>
                </h3>
                <div class="description">
                    Обновление информации о жанре (требуется авторизация администратора)
                </div>
                <div class="url">
                    PUT https://ivybook.ru/api/genres/1
                </div>
                
                <div class="parameters">
                    <h4>📥 Параметры запроса (JSON)</h4>
                    <table>
                        <thead>
                            <tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>name</td><td>string</td><td>❌</td><td>Название жанра</td></tr>
                            <tr><td>slug</td><td>string</td><td>❌</td><td>URL-идентификатор</td></tr>
                            <tr><td>description</td><td>string</td><td>❌</td><td>Описание жанра</td></tr>
                            <tr><td>parent_id</td><td>integer</td><td>❌</td><td>ID родительского жанра</td></tr>
                            <tr><td>image</td><td>string</td><td>❌</td><td>Путь к изображению</td></tr>
                            <tr><td>sort_order</td><td>integer</td><td>❌</td><td>Порядок сортировки</td></tr>
                            <tr><td>is_active</td><td>boolean</td><td>❌</td><td>Активен ли жанр</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (200 OK)</h4>
                    <div class="code-block">
{
    "success": true,
    "message": "Жанр успешно обновлен",
    "data": {
        "id": 1,
        "name": "Научная фантастика",
        "slug": "nauchnaya-fantastika",
        "description": "Книги в жанре научной фантастики",
        "is_active": true
    }
}
                    </div>
                </div>
            </div>
            
            <!-- Удаление жанра -->
            <div class="endpoint" id="genres-delete">
                <h3>
                    <span class="method method-delete">DELETE</span>
                    /api/genres/{id}
                    <span class="badge badge-admin">Только админ</span>
                </h3>
                <div class="description">
                    Удаление жанра (требуется авторизация администратора)
                </div>
                <div class="url">
                    DELETE https://ivybook.ru/api/genres/1
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (200 OK)</h4>
                    <div class="code-block">
{
    "success": true,
    "message": "Жанр успешно удален"
}
                    </div>
                </div>
                <div class="response">
                    <h4>⚠️ Ошибка при наличии книг (400 Bad Request)</h4>
                    <div class="code-block">
{
    "success": false,
    "message": "Невозможно удалить жанр, так как есть книги, привязанные к нему."
}
                    </div>
                </div>
            </div>
            
            <!-- Переключение активности -->
            <div class="endpoint" id="genres-toggle">
                <h3>
                    <span class="method method-patch">PATCH</span>
                    /api/genres/{id}/toggle-active
                    <span class="badge badge-admin">Только админ</span>
                </h3>
                <div class="description">
                    Переключение статуса активности жанра (требуется авторизация администратора)
                </div>
                <div class="url">
                    PATCH https://ivybook.ru/api/genres/1/toggle-active
                </div>
                
                <div class="response">
                    <h4>📤 Пример ответа (200 OK)</h4>
                    <div class="code-block">
{
    "success": true,
    "message": "Статус жанра изменен",
    "is_active": false
}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Авторы -->
<section id="authors">
    <h2>✍️ Управление авторами</h2>
    <p>Методы для управления авторами книг. Создание, редактирование и удаление доступны только администраторам.</p>
</section>

<!-- Список авторов -->
<div class="endpoint" id="authors-list">
    <h3>
        <span class="method method-get">GET</span>
        /api/authors
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">Получение списка всех авторов с возможностью поиска и сортировки</div>
    <div class="url">GET https://ivybook.ru/api/authors</div>
    <div class="parameters">
        <h4>📥 Параметры запроса (Query)</h4>
        <table>
            <thead><tr><th>Параметр</th><th>Тип</th><th>Описание</th></tr></thead>
            <tbody>
                <tr><td>search</td><td>string</td><td>Поиск по имени автора</td></tr>
                <tr><td>sort_by</td><td>string</td><td>Поле для сортировки (name, birth_date, created_at)</td></tr>
                <tr><td>sort_order</td><td>string</td><td>asc или desc</td></tr>
            </tbody>
        </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Александр Пушкин",
            "slug": "aleksandr-pushkin",
            "bio": "Великий русский поэт",
            "birth_date": "1799-06-06",
            "death_date": "1837-02-10",
            "photo": null,
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:00:00.000000Z"
        }
    ]
}
        </div>
    </div>
</div>

<!-- Просмотр автора -->
<div class="endpoint" id="authors-show">
    <h3>
        <span class="method method-get">GET</span>
        /api/authors/{id}
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">Получение информации о конкретном авторе</div>
    <div class="url">GET https://ivybook.ru/api/authors/1</div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Александр Пушкин",
        "slug": "aleksandr-pushkin",
        "bio": "Великий русский поэт",
        "birth_date": "1799-06-06",
        "death_date": "1837-02-10",
        "photo": null,
        "books": []
    }
}
        </div>
    </div>
</div>

<!-- Книги автора -->
<div class="endpoint" id="authors-books">
    <h3>
        <span class="method method-get">GET</span>
        /api/authors/{id}/books
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">Получение списка книг автора</div>
    <div class="url">GET https://ivybook.ru/api/authors/1/books</div>
</div>

<!-- Создание автора -->
<div class="endpoint" id="authors-create">
    <h3>
        <span class="method method-post">POST</span>
        /api/authors
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">Создание нового автора (требуется авторизация администратора)</div>
    <div class="url">POST https://ivybook.ru/api/authors</div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <table>
            <thead><tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th></tr></thead>
            <tbody>
                <tr><td>name</td><td>string</td><td>✅</td><td>Имя автора</td></tr>
                <tr><td>slug</td><td>string</td><td>❌</td><td>URL-идентификатор (генерируется автоматически)</td></tr>
                <tr><td>bio</td><td>string</td><td>❌</td><td>Биография автора</td></tr>
                <tr><td>birth_date</td><td>date</td><td>❌</td><td>Дата рождения (YYYY-MM-DD)</td></tr>
                <tr><td>death_date</td><td>date</td><td>❌</td><td>Дата смерти (должна быть после birth_date)</td></tr>
                <tr><td>photo</td><td>string</td><td>❌</td><td>Путь к фото</td></tr>
            </tbody>
        </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (201 Created)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Автор успешно создан",
    "data": {
        "id": 1,
        "name": "Александр Пушкин",
        "slug": "aleksandr-pushkin",
        "bio": "Великий русский поэт",
        "birth_date": "1799-06-06",
        "death_date": "1837-02-10"
    }
}
        </div>
    </div>
</div>

<!-- Обновление автора -->
<div class="endpoint" id="authors-update">
    <h3>
        <span class="method method-put">PUT</span>
        /api/authors/{id}
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">Обновление информации об авторе (требуется авторизация администратора)</div>
    <div class="url">PUT https://ivybook.ru/api/authors/1</div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <table>
            <thead><tr><th>Параметр</th><th>Тип</th><th>Описание</th></tr></thead>
            <tbody>
                <tr><td>name</td><td>string</td><td>Имя автора</td></tr>
                <tr><td>slug</td><td>string</td><td>URL-идентификатор</td></tr>
                <tr><td>bio</td><td>string</td><td>Биография автора</td></tr>
                <tr><td>birth_date</td><td>date</td><td>Дата рождения</td></tr>
                <tr><td>death_date</td><td>date</td><td>Дата смерти</td></tr>
                <tr><td>photo</td><td>string</td><td>Путь к фото</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Удаление автора -->
<div class="endpoint" id="authors-delete">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/authors/{id}
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">Удаление автора (требуется авторизация администратора)</div>
    <div class="url">DELETE https://ivybook.ru/api/authors/1</div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">{"success": true, "message": "Автор успешно удален"}</div>
        <h4>⚠️ Ошибка при наличии книг (400 Bad Request)</h4>
        <div class="code-block">{"success": false, "message": "Невозможно удалить автора, так как есть книги, привязанные к нему."}</div>
    </div>
</div>

<!-- Книги -->
<section id="books">
    <h2>📖 Управление книгами</h2>
    <p>Методы для управления книгами. Создание, редактирование и удаление доступны только администраторам.</p>
</section>

<!-- Список книг с фильтрацией -->
<div class="endpoint" id="books-list">
    <h3>
        <span class="method method-get">GET</span>
        /api/books
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Получение списка книг с фильтрацией, сортировкой и пагинацией
    </div>
    <div class="url">
        GET https://ivybook.ru/api/books
    </div>
</div>

<!-- Параметры фильтрации -->
<div class="endpoint" id="books-filters">
    <h3>
        <span class="method method-get">GET</span>
        /api/books (параметры)
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Параметры фильтрации и сортировки
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (Query)</h4>
        <table>
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Описание</th></tr>
            </thead>
            <tbody>
                <tr><td>search</td><td>string</td><td>Поиск по названию и описанию</td></tr>
                <tr><td>price_from</td><td>numeric</td><td>Минимальная цена</td></tr>
                <tr><td>price_to</td><td>numeric</td><td>Максимальная цена</td></tr>
                <tr><td>genre_id</td><td>integer</td><td>ID жанра</td></tr>
                <tr><td>author_id</td><td>integer</td><td>ID автора</td></tr>
                <tr><td>featured</td><td>boolean</td><td>Только рекомендуемые</td></tr>
                <tr><td>new</td><td>boolean</td><td>Только новинки</td></tr>
                <tr><td>bestseller</td><td>boolean</td><td>Только бестселлеры</td></tr>
                <tr><td>in_stock</td><td>boolean</td><td>Только в наличии</td></tr>
                <tr><td>sort_by</td><td>string</td><td>Сортировка: price, title, created_at, publication_year, pages</td></tr>
                <tr><td>sort_order</td><td>string</td><td>asc или desc</td></tr>
                <tr><td>per_page</td><td>integer</td><td>Количество на странице (max 100)</td></tr>
                <tr><td>page</td><td>integer</td><td>Номер страницы</td></tr>
            </tbody>
        </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Мастер и Маргарита",
                "slug": "master-i-margarita",
                "price": 890.00,
                "old_price": 1200.00,
                "discount_percent": 26,
                "cover_image": "/images/default-book.jpg",
                "is_in_stock": true,
                "is_featured": true,
                "is_new": true,
                "is_bestseller": true,
                "genre": {
                    "id": 1,
                    "name": "Роман",
                    "slug": "roman"
                },
                "author": {
                    "id": 1,
                    "name": "Михаил Булгаков",
                    "slug": "mihail-bulgakov"
                },
                "created_at": "2024-01-01T12:00:00.000000Z"
            }
        ],
        "total": 100,
        "per_page": 20
    }
}
        </div>
    </div>
</div>

<!-- Просмотр книги -->
<div class="endpoint" id="books-show">
    <h3>
        <span class="method method-get">GET</span>
        /api/books/{id}
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">Получение детальной информации о книге</div>
    <div class="url">GET https://ivybook.ru/api/books/1</div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Мастер и Маргарита",
        "slug": "master-i-margarita",
        "isbn": "978-5-699-12345-6",
        "description": "Знаменитый роман Михаила Булгакова...",
        "short_description": "Роман о любви и добре",
        "publisher": "Эксмо",
        "publication_year": 2020,
        "pages": 480,
        "language": "Русский",
        "cover_type": "hard",
        "format": "70x100/16",
        "weight": 650.00,
        "price": 890.00,
        "old_price": 1200.00,
        "discount_percent": 26,
        "quantity": 100,
        "is_in_stock": true,
        "is_featured": true,
        "is_new": true,
        "is_bestseller": true,
        "is_active": true,
        "cover_image": "/images/default-book.jpg",
        "genre": {
            "id": 1,
            "name": "Роман",
            "slug": "roman"
        },
        "author": {
            "id": 1,
            "name": "Михаил Булгаков",
            "slug": "mihail-bulgakov",
            "bio": "Михаил Афанасьевич Булгаков..."
        },
        "images": [],
        "reviews": [],
        "average_rating": null,
        "reviews_count": 0
    }
}
        </div>
    </div>
</div>

<!-- Похожие книги -->
<div class="endpoint" id="books-similar">
    <h3>
        <span class="method method-get">GET</span>
        /api/books/{id}/similar
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">Получение похожих книг (по жанру и автору)</div>
    <div class="url">GET https://ivybook.ru/api/books/1/similar?limit=4</div>
    <div class="parameters">
        <h4>📥 Параметры запроса (Query)</h4>
        <table>
            <thead><tr><th>Параметр</th><th>Тип</th><th>Описание</th></tr></thead>
            <tbody><tr><td>limit</td><td>integer</td><td>Количество книг (по умолчанию 6)</td></tr></tbody>
        </table>
    </div>
</div>

<!-- Отзывы книги -->
<div class="endpoint" id="books-reviews">
    <h3>
        <span class="method method-get">GET</span>
        /api/books/{id}/reviews
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">Получение отзывов о книге с пагинацией</div>
    <div class="url">GET https://ivybook.ru/api/books/1/reviews</div>
</div>

<!-- Создание книги -->
<div class="endpoint" id="books-create">
    <h3>
        <span class="method method-post">POST</span>
        /api/books
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">Создание новой книги (требуется авторизация администратора)</div>
    <div class="url">POST https://ivybook.ru/api/books</div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <table>
            <thead><tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th></tr></thead>
            <tbody>
                <tr><td>title</td><td>string</td><td>✅</td><td>Название книги</td></tr>
                <tr><td>author_id</td><td>integer</td><td>✅</td><td>ID автора</td></tr>
                <tr><td>price</td><td>numeric</td><td>✅</td><td>Цена</td></tr>
                <tr><td>quantity</td><td>integer</td><td>✅</td><td>Количество в наличии</td></tr>
                <tr><td>genre_id</td><td>integer</td><td>❌</td><td>ID жанра</td></tr>
                <tr><td>isbn</td><td>string</td><td>❌</td><td>ISBN книги</td></tr>
                <tr><td>description</td><td>text</td><td>❌</td><td>Полное описание</td></tr>
                <tr><td>short_description</td><td>string</td><td>❌</td><td>Краткое описание (max 500)</td></tr>
                <tr><td>publisher</td><td>string</td><td>❌</td><td>Издательство</td></tr>
                <tr><td>publication_year</td><td>integer</td><td>❌</td><td>Год издания</td></tr>
                <tr><td>pages</td><td>integer</td><td>❌</td><td>Количество страниц</td></tr>
                <tr><td>old_price</td><td>numeric</td><td>❌</td><td>Старая цена (для скидки)</td></tr>
                <tr><td>is_featured</td><td>boolean</td><td>❌</td><td>Рекомендуемая</td></tr>
                <tr><td>is_new</td><td>boolean</td><td>❌</td><td>Новинка</td></tr>
                <tr><td>is_bestseller</td><td>boolean</td><td>❌</td><td>Бестселлер</td></tr>
            </tbody>
        </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (201 Created)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Книга успешно создана",
    "data": { "id": 1, "title": "Мастер и Маргарита", ... }
}
        </div>
    </div>
</div>

<!-- Обновление книги -->
<div class="endpoint" id="books-update">
    <h3>
        <span class="method method-put">PUT</span>
        /api/books/{id}
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">Обновление информации о книге</div>
    <div class="url">PUT https://ivybook.ru/api/books/1</div>
</div>

<!-- Удаление книги -->
<div class="endpoint" id="books-delete">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/books/{id}
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">Удаление книги (только если нет в заказах)</div>
    <div class="url">DELETE https://ivybook.ru/api/books/1</div>
    <div class="response">
        <h4>⚠️ Ошибка при наличии в заказах (400 Bad Request)</h4>
        <div class="code-block">
{"success": false, "message": "Невозможно удалить книгу, так как она есть в заказах покупателей."}
        </div>
    </div>
</div>

<!-- Переключение статусов -->
<div class="endpoint" id="books-toggle">
    <h3>
        <span class="method method-patch">PATCH</span>
        /api/books/{id}/toggle-featured
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">Переключение статуса "Рекомендуемая"</div>
    <div class="url">PATCH https://ivybook.ru/api/books/1/toggle-featured</div>
    
    <h3 style="margin-top: 20px;">
        <span class="method method-patch">PATCH</span>
        /api/books/{id}/toggle-active
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">Переключение статуса активности</div>
    <div class="url">PATCH https://ivybook.ru/api/books/1/toggle-active</div>
</div>

<!-- Изображения книг -->
<section id="book-images">
    <h2>🖼️ Управление изображениями книг</h2>
    <p>Методы для управления изображениями книг. Загрузка, редактирование и удаление доступны только администраторам.</p>
</section>

<!-- Список изображений -->
<div class="endpoint" id="images-list">
    <h3>
        <span class="method method-get">GET</span>
        /api/books/{book_id}/images
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Получение списка всех изображений книги
    </div>
    <div class="url">
        GET https://ivybook.ru/api/books/1/images
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": [
        {
            "id": 1,
            "book_id": 1,
            "image_path": "/storage/books/abc123.jpg",
            "is_primary": true,
            "sort_order": 0,
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:00:00.000000Z"
        },
        {
            "id": 2,
            "book_id": 1,
            "image_path": "/storage/books/def456.jpg",
            "is_primary": false,
            "sort_order": 1,
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:00:00.000000Z"
        }
    ]
}
        </div>
    </div>
</div>

<!-- Основное изображение -->
<div class="endpoint" id="images-primary">
    <h3>
        <span class="method method-get">GET</span>
        /api/books/{book_id}/images/primary
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Получение основного изображения книги
    </div>
    <div class="url">
        GET https://ivybook.ru/api/books/1/images/primary
    </div>
</div>

<!-- Загрузка изображения -->
<div class="endpoint" id="images-upload">
    <h3>
        <span class="method method-post">POST</span>
        /api/books/{book_id}/images
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">
        Загрузка нового изображения для книги (требуется авторизация администратора)
    </div>
    <div class="url">
        POST https://ivybook.ru/api/books/1/images
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (multipart/form-data)</h4>
        <table>
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th>\
            </thead>
            <tbody>
                <tr><td>image</td><td>file</td><td>✅</td><td>Файл изображения (jpeg, png, jpg, gif, webp, max 5MB)</td>\
                <tr><td>is_primary</td><td>boolean</td><td>❌</td><td>Сделать основным (по умолчанию false)</td></tr>
                <tr><td>sort_order</td><td>integer</td><td>❌</td><td>Порядок сортировки (по умолчанию добавляется в конец)</td></tr>
            </tbody>
        </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (201 Created)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Изображение успешно загружено",
    "data": {
        "id": 3,
        "book_id": 1,
        "image_path": "/storage/books/xyz789.jpg",
        "is_primary": false,
        "sort_order": 2
    }
}
        </div>
    </div>
</div>

<!-- Обновление изображения -->
<div class="endpoint" id="images-update">
    <h3>
        <span class="method method-put">PUT</span>
        /api/book-images/{image_id}
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">
        Обновление информации об изображении (статус основного, порядок сортировки)
    </div>
    <div class="url">
        PUT https://ivybook.ru/api/book-images/1
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <table>
            <thead><tr><th>Параметр</th><th>Тип</th><th>Описание</th></tr></thead>
            <tbody>
                <tr><td>is_primary</td><td>boolean</td><td>Сделать основным (автоматически снимает флаг с других)</td></tr>
                <tr><td>sort_order</td><td>integer</td><td>Порядок сортировки</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Установить основным -->
<div class="endpoint" id="images-set-primary">
    <h3>
        <span class="method method-patch">PATCH</span>
        /api/book-images/{image_id}/set-primary
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">
        Установить изображение как основное
    </div>
    <div class="url">
        PATCH https://ivybook.ru/api/book-images/1/set-primary
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Основное изображение установлено",
    "data": {
        "id": 1,
        "image_path": "/storage/books/abc123.jpg",
        "is_primary": true
    }
}
        </div>
    </div>
</div>

<!-- Сортировка изображений -->
<div class="endpoint" id="images-reorder">
    <h3>
        <span class="method method-post">POST</span>
        /api/books/{book_id}/images/reorder
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">
        Массовое обновление порядка сортировки изображений
    </div>
    <div class="url">
        POST https://ivybook.ru/api/books/1/images/reorder
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <div class="code-block">
{
    "images": [
        {"id": 1, "sort_order": 0},
        {"id": 2, "sort_order": 1},
        {"id": 3, "sort_order": 2}
    ]
}
        </div>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Порядок сортировки обновлен"
}
        </div>
    </div>
</div>

<!-- Удаление изображения -->
<div class="endpoint" id="images-delete">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/book-images/{image_id}
        <span class="badge badge-admin">Только админ</span>
    </h3>
    <div class="description">
        Удаление изображения (файл удаляется из хранилища)
    </div>
    <div class="url">
        DELETE https://ivybook.ru/api/book-images/1
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Изображение успешно удалено"
}
        </div>
        <h4>✨ Особенности:</h4>
        <ul>
            <li>При удалении основного изображения, новое основное назначается автоматически</li>
            <li>Файл физически удаляется из папки storage</li>
        </ul>
    </div>
</div>

<!-- Корзина -->
<section id="cart">
    <h2>🛒 Корзина покупок</h2>
    <p>Корзина работает как для авторизованных пользователей, так и для гостей. Для гостей используется <code>session_id</code> в cookie.</p>
    <p><strong>Особенности:</strong></p>
    <ul>
        <li>При авторизации корзина гостя автоматически сливается с корзиной пользователя</li>
        <li>Перед добавлением проверяется наличие товара на складе</li>
        <li>Цена фиксируется в момент добавления и обновляется при изменении количества</li>
        <li>Cookie корзины гостя хранится 30 дней</li>
    </ul>
</section>

<!-- Просмотр корзины -->
<div class="endpoint" id="cart-get">
    <h3>
        <span class="method method-get">GET</span>
        /api/cart
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Получение содержимого корзины текущего пользователя или гостя
    </div>
    <div class="url">
        GET https://ivybook.ru/api/cart
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "id": 1,
        "items": [
            {
                "id": 1,
                "book_id": 1,
                "quantity": 2,
                "price": 890.00,
                "subtotal": 1780.00,
                "book": {
                    "id": 1,
                    "title": "Мастер и Маргарита",
                    "slug": "master-i-margarita",
                    "price": 890.00,
                    "cover_image": "/storage/books/abc123.jpg",
                    "is_in_stock": true
                }
            }
        ],
        "total": 1780.00,
        "items_count": 2
    }
}
        </div>
    </div>
</div>

<!-- Добавление товара -->
<div class="endpoint" id="cart-add">
    <h3>
        <span class="method method-post">POST</span>
        /api/cart/add
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Добавление товара в корзину
    </div>
    <div class="url">
        POST https://ivybook.ru/api/cart/add
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
         <table>
            <thead>
                 <tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th></tr>
            </thead>
            <tbody>
                 <tr><td>book_id</td><td>integer</td><td>✅</td><td>ID книги</td></tr>
                 <tr><td>quantity</td><td>integer</td><td>✅</td><td>Количество (от 1 до 99)</td></tr>
            </tbody>
         </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Товар добавлен в корзину",
    "data": {
        "cart_item_id": 1,
        "book_title": "Мастер и Маргарита",
        "quantity": 2
    }
}
        </div>
        <h4>⚠️ Ошибки</h4>
        <div class="code-block">
// Товар отсутствует на складе
{
    "success": false,
    "message": "Товар отсутствует на складе"
}

// Недостаточно товара
{
    "success": false,
    "message": "Доступно только 5 шт."
}
        </div>
    </div>
</div>

<!-- Обновление количества -->
<div class="endpoint" id="cart-update">
    <h3>
        <span class="method method-put">PUT</span>
        /api/cart/items/{cart_item_id}
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Обновление количества товара в корзине (при quantity = 0 товар удаляется)
    </div>
    <div class="url">
        PUT https://ivybook.ru/api/cart/items/1
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
         <table>
            <thead><tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th></tr></thead>
            <tbody><tr><td>quantity</td><td>integer</td><td>✅</td><td>Новое количество (0-99)</td></tr></tbody>
         </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Количество обновлено",
    "data": {
        "quantity": 3,
        "subtotal": 2670.00
    }
}
        </div>
    </div>
</div>

<!-- Удаление товара -->
<div class="endpoint" id="cart-remove">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/cart/items/{cart_item_id}
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Удаление конкретного товара из корзины
    </div>
    <div class="url">
        DELETE https://ivybook.ru/api/cart/items/1
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Товар удален из корзины"
}
        </div>
    </div>
</div>

<!-- Очистка корзины -->
<div class="endpoint" id="cart-clear">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/cart/clear
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Полная очистка корзины
    </div>
    <div class="url">
        DELETE https://ivybook.ru/api/cart/clear
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Корзина очищена"
}
        </div>
    </div>
</div>

<!-- Слияние корзин -->
<div class="endpoint" id="cart-merge">
    <h3>
        <span class="method method-post">POST</span>
        /api/cart/merge
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Слияние гостевой корзины с корзиной авторизованного пользователя. Вызывается после входа в систему.
    </div>
    <div class="url">
        POST https://ivybook.ru/api/cart/merge
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Корзина успешно объединена"
}
        </div>
        <h4>⚠️ Ошибки</h4>
        <div class="code-block">
{
    "success": false,
    "message": "Гостевая корзина не найдена"
}
        </div>
    </div>
</div>

<!-- Примеры работы с корзиной -->
<div class="endpoint">
    <h3>📝 Примеры работы с корзиной</h3>
    <div class="code-block">
// 1. Гость добавляет товары в корзину
POST /api/cart/add
{
    "book_id": 1,
    "quantity": 2
}

// 2. Гость авторизуется
POST /api/login
{
    "email": "user@example.com",
    "password": "password"
}

// 3. После авторизации вызываем слияние корзин
POST /api/cart/merge

// 4. Теперь корзина гостя объединена с корзиной пользователя
GET /api/cart
    </div>
</div>
        
        <!-- Избранное -->
<section id="favorites">
    <h2>❤️ Избранное</h2>
    <p>Методы для управления избранными книгами пользователя. <strong>Требуется авторизация.</strong></p>
</section>

<!-- Список избранного -->
<div class="endpoint" id="favorites-list">
    <h3>
        <span class="method method-get">GET</span>
        /api/favorites
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">Получение списка избранных книг с пагинацией</div>
    <div class="url">GET https://ivybook.ru/api/favorites?per_page=20&page=1</div>
</div>

<!-- Добавить в избранное -->
<div class="endpoint" id="favorites-add">
    <h3>
        <span class="method method-post">POST</span>
        /api/favorites/{book_id}
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">Добавление книги в избранное</div>
    <div class="url">POST https://ivybook.ru/api/favorites/1</div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Книга добавлена в избранное",
    "data": {
        "book_id": 1,
        "title": "Мастер и Маргарита"
    }
}
        </div>
    </div>
</div>

<!-- Удалить из избранного -->
<div class="endpoint" id="favorites-remove">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/favorites/{book_id}
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">Удаление книги из избранного</div>
    <div class="url">DELETE https://ivybook.ru/api/favorites/1</div>
</div>

<!-- Проверка статуса -->
<div class="endpoint" id="favorites-check">
    <h3>
        <span class="method method-get">GET</span>
        /api/favorites/{book_id}/check
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">Проверка, находится ли книга в избранном</div>
    <div class="url">GET https://ivybook.ru/api/favorites/1/check</div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "is_favorite": true
}
        </div>
    </div>
</div>

<!-- ID всех избранных книг -->
<div class="endpoint" id="favorites-ids">
    <h3>
        <span class="method method-get">GET</span>
        /api/favorites/ids
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">Получение массива ID всех избранных книг (для синхронизации на фронте)</div>
    <div class="url">GET https://ivybook.ru/api/favorites/ids</div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "data": [1, 3, 5, 7]
}
        </div>
    </div>
</div>

<!-- Заказы -->
<section id="orders">
    <h2>📦 Заказы</h2>
    <p>Методы для управления заказами. Создание и просмотр доступны авторизованным пользователям. Управление заказами (смена статусов) доступно менеджерам и администраторам.</p>
</section>

<!-- Список заказов пользователя -->
<div class="endpoint" id="orders-list">
    <h3>
        <span class="method method-get">GET</span>
        /api/orders
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Получение списка заказов текущего пользователя с пагинацией
    </div>
    <div class="url">
        GET https://ivybook.ru/api/orders?per_page=20&page=1&status=processing
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (Query)</h4>
        <table>
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Описание</th></tr>
            </thead>
            <tbody>
                <tr><td>per_page</td><td>integer</td><td>Количество на странице (max 100)</td></tr>
                <tr><td>page</td><td>integer</td><td>Номер страницы</td></tr>
                <tr><td>status</td><td>string</td><td>Фильтр по статусу заказа</td></tr>
            </tbody>
        </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "order_number": "ORD-5F2A8B9C",
                "status": "processing",
                "total_amount": 1190.00,
                "delivery_method": "courier",
                "delivery_address": "г. Москва, ул. Тверская, д. 1",
                "payment_status": "paid",
                "created_at": "2024-01-01T12:00:00.000000Z",
                "items": [...]
            }
        ],
        "total": 5
    }
}
        </div>
    </div>
</div>

<!-- Детали заказа -->
<div class="endpoint" id="orders-show">
    <h3>
        <span class="method method-get">GET</span>
        /api/orders/{id}
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Получение детальной информации о заказе. Пользователь видит только свои заказы, менеджеры и админы — любые.
    </div>
    <div class="url">
        GET https://ivybook.ru/api/orders/1
    </div>
</div>

<!-- Оформление заказа -->
<div class="endpoint" id="orders-create">
    <h3>
        <span class="method method-post">POST</span>
        /api/orders
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Оформление заказа из текущей корзины. После создания заказа корзина очищается, количество товаров на складе уменьшается.
    </div>
    <div class="url">
        POST https://ivybook.ru/api/orders
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <table>
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th></tr>
            </thead>
            <tbody>
                <tr><td>delivery_method</td><td>string</td><td>✅</td><td>Способ доставки (pickup, courier, post)</td></tr>
                <tr><td>delivery_address</td><td>string</td><td>✅</td><td>Адрес доставки</td></tr>
                <tr><td>delivery_date</td><td>date</td><td>❌</td><td>Желаемая дата доставки</td></tr>
                <tr><td>payment_method</td><td>string</td><td>✅</td><td>Способ оплаты (card, cash, online)</td></tr>
                <tr><td>comment</td><td>string</td><td>❌</td><td>Комментарий к заказу</td></tr>
            </tbody>
        </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (201 Created)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Заказ успешно создан",
    "data": {
        "id": 1,
        "order_number": "ORD-5F2A8B9C",
        "status": "new",
        "total_amount": 1190.00,
        "items": [...]
    }
}
        </div>
        <h4>⚠️ Ошибки</h4>
        <div class="code-block">
// Корзина пуста
{
    "success": false,
    "message": "Корзина пуста"
}

// Товар отсутствует на складе
{
    "success": false,
    "message": "Товар \"Мастер и Маргарита\" отсутствует на складе"
}
        </div>
    </div>
</div>

<!-- Отмена заказа -->
<div class="endpoint" id="orders-cancel">
    <h3>
        <span class="method method-post">POST</span>
        /api/orders/{id}/cancel
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Отмена заказа. Доступна только для заказов в статусах "new" или "pending". При отмене товары возвращаются на склад.
    </div>
    <div class="url">
        POST https://ivybook.ru/api/orders/1/cancel
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Заказ отменен"
}
        </div>
    </div>
</div>

<!-- Управление заказами (админ/менеджер) -->
<section id="orders-admin">
    <h2>🔧 Управление заказами</h2>
    <p>Методы доступны пользователям с ролями <code>admin</code> и <code>manager</code>.</p>
</section>

<!-- Все заказы -->
<div class="endpoint" id="orders-admin-list">
    <h3>
        <span class="method method-get">GET</span>
        /api/admin/orders
        <span class="badge badge-admin">Только админ/менеджер</span>
    </h3>
    <div class="description">
        Получение списка всех заказов с возможностью фильтрации и поиска
    </div>
    <div class="url">
        GET https://ivybook.ru/api/admin/orders?per_page=20&status=processing&search=ORD-123
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (Query)</h4>
        <table>
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Описание</th></tr>
            </thead>
            <tbody>
                <tr><td>per_page</td><td>integer</td><td>Количество на странице</td></tr>
                <tr><td>status</td><td>string</td><td>Фильтр по статусу</td></tr>
                <tr><td>search</td><td>string</td><td>Поиск по номеру заказа, имени или email клиента</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Смена статуса заказа -->
<div class="endpoint" id="orders-status">
    <h3>
        <span class="method method-put">PUT</span>
        /api/admin/orders/{id}/status
        <span class="badge badge-admin">Только админ/менеджер</span>
    </h3>
    <div class="description">
        Изменение статуса заказа. Доступные статусы: new, pending, processing, shipped, delivered, cancelled, refunded
    </div>
    <div class="url">
        PUT https://ivybook.ru/api/admin/orders/1/status
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <div class="code-block">
{
    "status": "processing",
    "admin_comment": "Заказ передан в обработку"
}
        </div>
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Статус заказа обновлен",
    "data": {
        "id": 1,
        "status": "processing",
        "admin_comment": "Заказ передан в обработку"
    }
}
        </div>
    </div>
</div>

<!-- Смена статуса оплаты -->
<div class="endpoint" id="orders-payment">
    <h3>
        <span class="method method-put">PUT</span>
        /api/admin/orders/{id}/payment-status
        <span class="badge badge-admin">Только админ/менеджер</span>
    </h3>
    <div class="description">
        Изменение статуса оплаты. Доступные статусы: pending, paid, failed, refunded
    </div>
    <div class="url">
        PUT https://ivybook.ru/api/admin/orders/1/payment-status
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <div class="code-block">
{
    "payment_status": "paid"
}
        </div>
    </div>
</div>

<!-- Статусы заказов -->
<div class="endpoint">
    <h3>📋 Статусы заказов</h3>
    <div class="description">
        <ul>
            <li><strong>new</strong> - Новый заказ</li>
            <li><strong>pending</strong> - Ожидает подтверждения</li>
            <li><strong>processing</strong> - В обработке</li>
            <li><strong>shipped</strong> - Отправлен</li>
            <li><strong>delivered</strong> - Доставлен</li>
            <li><strong>cancelled</strong> - Отменен</li>
            <li><strong>refunded</strong> - Возврат</li>
        </ul>
    </div>
</div>

<div class="endpoint">
    <h3>💰 Статусы оплаты</h3>
    <div class="description">
        <ul>
            <li><strong>pending</strong> - Ожидает оплаты</li>
            <li><strong>paid</strong> - Оплачен</li>
            <li><strong>failed</strong> - Ошибка оплаты</li>
            <li><strong>refunded</strong> - Возврат средств</li>
        </ul>
    </div>
</div>

<!-- Стоимость доставки -->
<div class="endpoint">
    <h3>🚚 Стоимость доставки</h3>
    <div class="description">
        <ul>
            <li><strong>pickup</strong> - Самовывоз: 0 ₽</li>
            <li><strong>courier</strong> - Курьер: 300 ₽</li>
            <li><strong>post</strong> - Почта: 250 ₽</li>
        </ul>
    </div>
</div>

<!-- Отзывы -->
<section id="reviews">
    <h2>⭐ Отзывы на книги</h2>
    <p>Методы для работы с отзывами. Пользователи могут оставлять отзывы только на купленные книги. Отзывы проходят модерацию.</p>
</section>

<!-- Отзывы книги -->
<div class="endpoint" id="reviews-list">
    <h3>
        <span class="method method-get">GET</span>
        /api/books/{book_id}/reviews
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Получение списка одобренных отзывов на книгу с пагинацией и распределением оценок
    </div>
    <div class="url">
        GET https://ivybook.ru/api/books/1/reviews?per_page=20&page=1
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "reviews": {
            "current_page": 1,
            "data": [
                {
                    "id": 1,
                    "rating": 5,
                    "title": "Отличная книга!",
                    "comment": "Прочитал на одном дыхании. Рекомендую всем!",
                    "user_name": "Иван Петров",
                    "created_at": "2024-01-01T12:00:00.000000Z",
                    "updated_at": "2024-01-01T12:00:00.000000Z"
                }
            ],
            "total": 15
        },
        "average_rating": 4.7,
        "total_reviews": 15,
        "rating_distribution": {
            "1": 0,
            "2": 1,
            "3": 2,
            "4": 5,
            "5": 7
        }
    }
}
        </div>
    </div>
</div>

<!-- Мой отзыв на книгу -->
<div class="endpoint" id="reviews-my">
    <h3>
        <span class="method method-get">GET</span>
        /api/books/{book_id}/reviews/user
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Получение отзыва текущего пользователя на конкретную книгу
    </div>
    <div class="url">
        GET https://ivybook.ru/api/books/1/reviews/user
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "id": 1,
        "rating": 5,
        "title": "Отличная книга!",
        "comment": "Прочитал на одном дыхании",
        "is_approved": false
    }
}
        </div>
    </div>
</div>

<!-- Создание отзыва -->
<div class="endpoint" id="reviews-create">
    <h3>
        <span class="method method-post">POST</span>
        /api/books/{book_id}/reviews
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Оставить отзыв на книгу. <strong>Условия:</strong> книга должна быть куплена и доставлена (статус заказа "delivered"). Отзыв отправляется на модерацию.
    </div>
    <div class="url">
        POST https://ivybook.ru/api/books/1/reviews
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <table>
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th>\
            </thead>
            <tbody>
                <tr><td>rating</td><td>integer</td><td>✅</td><td>Оценка от 1 до 5</td>\
                 <tr><td>title</td><td>string</td><td>❌</td><td>Заголовок отзыва (max 255)</td></tr>
                 <tr><td>comment</td><td>string</td><td>❌</td><td>Текст отзыва (max 5000)</td></tr>
            </tbody>
         </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (201 Created)</h4>
        <div class="code-block">
{
    "success": true,
    "message": "Отзыв отправлен на модерацию",
    "data": {
        "id": 1,
        "rating": 5,
        "title": "Отличная книга!",
        "comment": "Прочитал на одном дыхании",
        "is_approved": false
    }
}
        </div>
        <h4>⚠️ Ошибки</h4>
        <div class="code-block">
// Книга не куплена
{
    "success": false,
    "message": "Вы можете оставить отзыв только на купленную книгу"
}

// Уже есть отзыв
{
    "success": false,
    "message": "Вы уже оставили отзыв на эту книгу"
}
        </div>
    </div>
</div>

<!-- Редактирование отзыва -->
<div class="endpoint" id="reviews-update">
    <h3>
        <span class="method method-put">PUT</span>
        /api/reviews/{review_id}
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Редактирование своего отзыва. Доступно только для неодобренных отзывов.
    </div>
    <div class="url">
        PUT https://ivybook.ru/api/reviews/1
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <div class="code-block">
{
    "rating": 4,
    "title": "Хорошая книга",
    "comment": "Обновленный комментарий"
}
        </div>
    </div>
</div>

<!-- Удаление отзыва -->
<div class="endpoint" id="reviews-delete">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/reviews/{review_id}
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Удаление своего отзыва. Пользователь может удалить только свой отзыв. Админ/менеджер может удалить любой.
    </div>
    <div class="url">
        DELETE https://ivybook.ru/api/reviews/1
    </div>
</div>

<!-- Модерация отзывов -->
<section id="reviews-admin">
    <h2>🔧 Модерация отзывов</h2>
    <p>Методы доступны пользователям с ролями <code>admin</code> и <code>manager</code>.</p>
</section>

<!-- Все отзывы -->
<div class="endpoint" id="reviews-admin-list">
    <h3>
        <span class="method method-get">GET</span>
        /api/admin/reviews
        <span class="badge badge-admin">Только админ/менеджер</span>
    </h3>
    <div class="description">
        Получение списка всех отзывов с возможностью фильтрации по статусу и книге
    </div>
    <div class="url">
        GET https://ivybook.ru/api/admin/reviews?per_page=20&status=pending&book_id=1
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (Query)</h4>
        
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Описание</th>\
            </thead>
            <tbody>
                 <tr><td>per_page</td><td>integer</td><td>Количество на странице</td></tr>
                 <tr><td>status</td><td>string</td><td>approved (одобренные) или pending (на модерации)</td></tr>
                 <tr><td>book_id</td><td>integer</td><td>Фильтр по книге</td></tr>
            </tbody>
         </table>
    </div>
</div>

<!-- Одобрить отзыв -->
<div class="endpoint" id="reviews-approve">
    <h3>
        <span class="method method-patch">PATCH</span>
        /api/admin/reviews/{review_id}/approve
        <span class="badge badge-admin">Только админ/менеджер</span>
    </h3>
    <div class="description">
        Одобрение отзыва. После одобрения отзыв становится видимым для всех пользователей.
    </div>
    <div class="url">
        PATCH https://ivybook.ru/api/admin/reviews/1/approve
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Отзыв одобрен",
    "data": {
        "id": 1,
        "is_approved": true
    }
}
        </div>
    </div>
</div>

<!-- Отклонить отзыв -->
<div class="endpoint" id="reviews-reject">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/admin/reviews/{review_id}/reject
        <span class="badge badge-admin">Только админ/менеджер</span>
    </h3>
    <div class="description">
        Отклонение отзыва. Отзыв удаляется без возможности восстановления.
    </div>
    <div class="url">
        DELETE https://ivybook.ru/api/admin/reviews/1/reject
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Отзыв отклонен и удален"
}
        </div>
    </div>
</div>

<!-- Правила отзывов -->
<div class="endpoint">
    <h3>📋 Правила оставления отзывов</h3>
    <div class="description">
        <ul>
            <li><strong>Только после покупки:</strong> оставить отзыв можно только на книгу, которая была куплена и доставлена</li>
            <li><strong>Один отзыв:</strong> один пользователь может оставить только один отзыв на книгу</li>
            <li><strong>Модерация:</strong> все отзывы проходят проверку перед публикацией</li>
            <li><strong>Редактирование:</strong> неодобренные отзывы можно редактировать</li>
            <li><strong>Оценка:</strong> от 1 до 5 звезд (обязательное поле)</li>
        </ul>
    </div>
</div>

<!-- Мероприятия -->
<section id="events">
    <h2>🎪 Мероприятия</h2>
    <p>Методы для работы с мероприятиями: встречи с авторами, презентации, лекции, мастер-классы.</p>
    <p><strong>Типы мероприятий:</strong></p>
    <ul>
        <li><code>author_meeting</code> — Встреча с автором</li>
        <li><code>presentation</code> — Презентация книги</li>
        <li><code>lecture</code> — Лекция</li>
        <li><code>workshop</code> — Мастер-класс</li>
    </ul>
</section>

<!-- Список мероприятий -->
<div class="endpoint" id="events-list">
    <h3>
        <span class="method method-get">GET</span>
        /api/events
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Получение списка мероприятий с фильтрацией и пагинацией
    </div>
    <div class="url">
        GET https://ivybook.ru/api/events
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (Query)</h4>
        
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Описание</th>\
            </thead>
            <tbody>
                <tr><td>event_type</td><td>string</td><td>Фильтр по типу мероприятия</td>\
                <tr><td>author_id</td><td>integer</td><td>Фильтр по автору</td>\
                <tr><td>upcoming</td><td>boolean</td><td>Только предстоящие мероприятия</td>\
                <tr><td>sort_by</td><td>string</td><td>Сортировка: start_date, title, price</td>\
                <tr><td>sort_order</td><td>string</td><td>asc или desc</td>\
                <tr><td>per_page</td><td>integer</td><td>Количество на странице (max 100)</td>\
            </tbody>
         ?>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Встреча с Михаилом Булгаковым",
                "slug": "vstrecha-s-mihailom-bulgakovym",
                "event_type": "author_meeting",
                "start_date": "2024-12-25 18:00:00",
                "end_date": null,
                "location": "Москва, ул. Тверская, д. 1",
                "price": 500,
                "image": null,
                "available_seats": 48,
                "is_full": false,
                "author": {
                    "id": 1,
                    "name": "Михаил Булгаков"
                }
            }
        ],
        "total": 5
    }
}
        </div>
    </div>
</div>

<!-- Детали мероприятия -->
<div class="endpoint" id="events-show">
    <h3>
        <span class="method method-get">GET</span>
        /api/events/{id}
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Получение детальной информации о мероприятии
    </div>
    <div class="url">
        GET https://ivybook.ru/api/events/1
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Встреча с Михаилом Булгаковым",
        "slug": "vstrecha-s-mihailom-bulgakovym",
        "description": "Увлекательная встреча с великим писателем...",
        "event_type": "author_meeting",
        "start_date": "2024-12-25 18:00:00",
        "location": "Москва, ул. Тверская, д. 1",
        "price": 500,
        "max_participants": 50,
        "registered_count": 2,
        "available_seats": 48,
        "is_full": false,
        "is_active": true,
        "author": {...},
        "registrations": [...]
    }
}
        </div>
    </div>
</div>

<!-- Поиск по slug -->
<div class="endpoint">
    <h3>
        <span class="method method-get">GET</span>
        /api/events/slug/{slug}
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Получение мероприятия по slug
    </div>
    <div class="url">
        GET https://ivybook.ru/api/events/slug/vstrecha-s-mihailom-bulgakovym
    </div>
</div>

<!-- Регистрация на мероприятие -->
<div class="endpoint" id="events-register">
    <h3>
        <span class="method method-post">POST</span>
        /api/events/{id}/register
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Регистрация на мероприятие. Проверяется наличие свободных мест.
    </div>
    <div class="url">
        POST https://ivybook.ru/api/events/1/register
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        
            <thead><tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th> </thead>
            <tbody>
                <tr><td>attendees_count</td><td>integer</td><td>✅</td><td>Количество участников (от 1 до 10)</td></tr>
                <tr><td>comment</td><td>string</td><td>❌</td><td>Комментарий к регистрации</td></tr>
            </tbody>
        ?
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Вы успешно зарегистрированы",
    "data": {
        "id": 1,
        "event_id": 1,
        "attendees_count": 2,
        "status": "pending"
    }
}
        </div>
    </div>
</div>

<!-- Мои регистрации -->
<div class="endpoint" id="events-my">
    <h3>
        <span class="method method-get">GET</span>
        /api/my/registrations
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Получение списка всех регистраций текущего пользователя
    </div>
    <div class="url">
        GET https://ivybook.ru/api/my/registrations
    </div>
</div>

<!-- Проверка регистрации -->
<div class="endpoint" id="events-check">
    <h3>
        <span class="method method-get">GET</span>
        /api/events/{id}/check-registration
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Проверка, зарегистрирован ли пользователь на мероприятие
    </div>
    <div class="url">
        GET https://ivybook.ru/api/events/1/check-registration
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "is_registered": true
}
        </div>
    </div>
</div>

<!-- Отмена регистрации -->
<div class="endpoint" id="events-cancel">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/events/{id}/register
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Отмена регистрации на мероприятие
    </div>
    <div class="url">
        DELETE https://ivybook.ru/api/events/1/register
    </div>
</div>

<!-- Управление мероприятиями -->
<section id="events-admin">
    <h2>🔧 Управление мероприятиями</h2>
    <p>Методы доступны пользователям с ролями <code>admin</code> и <code>manager</code>.</p>
</section>

<!-- Создание мероприятия -->
<div class="endpoint" id="events-create">
    <h3>
        <span class="method method-post">POST</span>
        /api/admin/events
        <span class="badge badge-admin">Админ/Менеджер</span>
    </h3>
    <div class="description">
        Создание нового мероприятия
    </div>
    <div class="url">
        POST https://ivybook.ru/api/admin/events
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th>\
            </thead>
            <tbody>
                <tr><td>title</td><td>string</td><td>✅</td><td>Название мероприятия</td></tr>
                <tr><td>description</td><td>text</td><td>✅</td><td>Описание мероприятия</td></tr>
                <tr><td>event_type</td><td>string</td><td>✅</td><td>Тип: author_meeting, presentation, lecture, workshop</td></tr>
                <tr><td>start_date</td><td>datetime</td><td>✅</td><td>Дата и время начала</td></tr>
                <tr><td>location</td><td>string</td><td>✅</td><td>Место проведения</td></tr>
                <tr><td>author_id</td><td>integer</td><td>❌</td><td>ID автора (для встреч)</td></tr>
                <tr><td>end_date</td><td>datetime</td><td>❌</td><td>Дата и время окончания</td></tr>
                <tr><td>max_participants</td><td>integer</td><td>❌</td><td>Максимальное количество участников</td></tr>
                <tr><td>price</td><td>numeric</td><td>❌</td><td>Стоимость (по умолчанию 0)</td></tr>
                <tr><td>image</td><td>string</td><td>❌</td><td>Путь к изображению</td></tr>
                <tr><td>is_active</td><td>boolean</td><td>❌</td><td>Активность (по умолчанию true)</td></tr>
            </tbody>
        ~
    </div>
</div>

<!-- Обновление мероприятия -->
<div class="endpoint" id="events-update">
    <h3>
        <span class="method method-put">PUT</span>
        /api/admin/events/{id}
        <span class="badge badge-admin">Админ/Менеджер</span>
    </h3>
    <div class="description">
        Обновление информации о мероприятии
    </div>
    <div class="url">
        PUT https://ivybook.ru/api/admin/events/1
    </div>
</div>

<!-- Удаление мероприятия -->
<div class="endpoint" id="events-delete">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/admin/events/{id}
        <span class="badge badge-admin">Админ/Менеджер</span>
    </h3>
    <div class="description">
        Удаление мероприятия (только если нет зарегистрированных участников)
    </div>
    <div class="url">
        DELETE https://ivybook.ru/api/admin/events/1
    </div>
</div>

<!-- Список участников -->
<div class="endpoint" id="events-registrations">
    <h3>
        <span class="method method-get">GET</span>
        /api/admin/events/{id}/registrations
        <span class="badge badge-admin">Админ/Менеджер</span>
    </h3>
    <div class="description">
        Получение списка участников мероприятия
    </div>
    <div class="url">
        GET https://ivybook.ru/api/admin/events/1/registrations
    </div>
</div>

<!-- Подтверждение регистрации -->
<div class="endpoint" id="events-confirm">
    <h3>
        <span class="method method-patch">PATCH</span>
        /api/admin/registrations/{id}/confirm
        <span class="badge badge-admin">Админ/Менеджер</span>
    </h3>
    <div class="description">
        Подтверждение регистрации участника
    </div>
    <div class="url">
        PATCH https://ivybook.ru/api/admin/registrations/1/confirm
    </div>
</div>

<!-- Отмена регистрации (админ) -->
<div class="endpoint">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/admin/registrations/{id}
        <span class="badge badge-admin">Админ/Менеджер</span>
    </h3>
    <div class="description">
        Принудительная отмена регистрации (администратором)
    </div>
    <div class="url">
        DELETE https://ivybook.ru/api/admin/registrations/1
    </div>
</div>

<!-- Статусы регистрации -->
<div class="endpoint">
    <h3>📋 Статусы регистрации</h3>
    <div class="description">
        <ul>
            <li><strong>pending</strong> — Ожидает подтверждения</li>
            <li><strong>confirmed</strong> — Подтверждена</li>
            <li><strong>cancelled</strong> — Отменена</li>
        </ul>
    </div>
</div>

<!-- Буккроссинг -->
<section id="bookcrossing">
    <h2>📖 Буккроссинг (Bookcrossing)</h2>
    <p>Система обмена книгами. Пользователи могут добавлять свои книги, брать книги других участников и возвращать их.</p>
    <p><strong>Статусы книг:</strong></p>
    <ul>
        <li><code>available</code> — Доступна для взятия</li>
        <li><code>reserved</code> — Зарезервирована</li>
        <li><code>taken</code> — Взята</li>
    </ul>
</section>

<!-- Список книг -->
<div class="endpoint" id="bookcrossing-list">
    <h3>
        <span class="method method-get">GET</span>
        /api/bookcrossing
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Получение списка книг для буккроссинга с фильтрацией и пагинацией
    </div>
    <div class="url">
        GET https://ivybook.ru/api/bookcrossing
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (Query)</h4>
        
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Описание</th>\
            </thead>
            <tbody>
                <tr><td>status</td><td>string</td><td>Фильтр по статусу (available, reserved, taken)</td>\
                <tr><td>search</td><td>string</td><td>Поиск по названию или автору</td>\
                <tr><td>location</td><td>string</td><td>Фильтр по местоположению</td>\
                <tr><td>sort_by</td><td>string</td><td>Сортировка: title, author, status, created_at</td>\
                <tr><td>sort_order</td><td>string</td><td>asc или desc</td>\
                51ec<td>per_page</td><td>integer</td><td>Количество на странице (max 100)</td>\
            </tbody>
        </table>
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Мастер и Маргарита",
                "author": "Михаил Булгаков",
                "status": "available",
                "location": "Москва, м. Арбатская",
                "image": null,
                "owner": {
                    "id": 1,
                    "name": "Иван Петров"
                }
            }
        ],
        "total": 10
    }
}
        </div>
    </div>
</div>

<!-- Детали книги -->
<div class="endpoint" id="bookcrossing-show">
    <h3>
        <span class="method method-get">GET</span>
        /api/bookcrossing/{id}
        <span class="badge badge-public">Публичный</span>
    </h3>
    <div class="description">
        Получение детальной информации о книге
    </div>
    <div class="url">
        GET https://ivybook.ru/api/bookcrossing/1
    </div>
    <div class="response">
        <h4>📤 Пример ответа (200 OK)</h4>
        <div class="code-block">
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Мастер и Маргарита",
        "author": "Михаил Булгаков",
        "description": "Классический роман в отличном состоянии",
        "status": "available",
        "location": "Москва, м. Арбатская",
        "image": null,
        "owner": {
            "id": 1,
            "name": "Иван Петров"
        },
        "created_at": "2024-01-01T12:00:00.000000Z"
    }
}
        </div>
    </div>
</div>

<!-- Добавление книги -->
<div class="endpoint" id="bookcrossing-add">
    <h3>
        <span class="method method-post">POST</span>
        /api/bookcrossing
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Добавление книги в буккроссинг. Книга становится доступной для других пользователей.
    </div>
    <div class="url">
        POST https://ivybook.ru/api/bookcrossing
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        
            <thead>
                <tr><th>Параметр</th><th>Тип</th><th>Обязательный</th><th>Описание</th>\
            </thead>
            <tbody>
                <tr><td>title</td><td>string</td><td>✅</td><td>Название книги</td>\
                <tr><td>author</td><td>string</td><td>✅</td><td>Автор книги</td>\
                <tr><td>description</td><td>string</td><td>❌</td><td>Описание состояния книги</td>\
                <tr><td>location</td><td>string</td><td>❌</td><td>Местоположение (город, станция метро)</td>\
                <tr><td>image</td><td>string</td><td>❌</td><td>Путь к изображению</td>\
            </tbody>
        </table>
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Книга добавлена в буккроссинг",
    "data": {
        "id": 1,
        "title": "Мастер и Маргарита",
        "status": "available"
    }
}
        </div>
    </div>
</div>

<!-- Взять книгу -->
<div class="endpoint" id="bookcrossing-take">
    <h3>
        <span class="method method-post">POST</span>
        /api/bookcrossing/{id}/take
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Взять книгу. Доступно только для книг со статусом <code>available</code>. Нельзя взять свою собственную книгу.
    </div>
    <div class="url">
        POST https://ivybook.ru/api/bookcrossing/1/take
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Книга успешно взята. Приятного чтения!",
    "data": {
        "id": 1,
        "status": "taken",
        "taken_by": {
            "id": 2,
            "name": "Петр Сидоров"
        },
        "taken_at": "2024-01-01T12:00:00.000000Z"
    }
}
        </div>
    </div>
</div>

<!-- Вернуть книгу -->
<div class="endpoint" id="bookcrossing-return">
    <h3>
        <span class="method method-post">POST</span>
        /api/bookcrossing/{id}/return
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Вернуть книгу. Доступно только для книг, взятых текущим пользователем.
    </div>
    <div class="url">
        POST https://ivybook.ru/api/bookcrossing/1/return
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Книга возвращена. Спасибо!",
    "data": {
        "id": 1,
        "status": "available",
        "released_at": "2024-01-15T12:00:00.000000Z"
    }
}
        </div>
    </div>
</div>

<!-- Мои книги -->
<div class="endpoint" id="bookcrossing-my">
    <h3>
        <span class="method method-get">GET</span>
        /api/my/bookcrossing
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Получение списка книг, добавленных текущим пользователем
    </div>
    <div class="url">
        GET https://ivybook.ru/api/my/bookcrossing
    </div>
</div>

<!-- Взятые книги -->
<div class="endpoint" id="bookcrossing-taken">
    <h3>
        <span class="method method-get">GET</span>
        /api/my/taken-books
        <span class="badge badge-auth">Требует авторизацию</span>
    </h3>
    <div class="description">
        Получение списка книг, взятых текущим пользователем
    </div>
    <div class="url">
        GET https://ivybook.ru/api/my/taken-books
    </div>
</div>

<!-- Управление буккроссингом -->
<section id="bookcrossing-admin">
    <h2>🔧 Управление буккроссингом</h2>
    <p>Методы доступны пользователям с ролями <code>admin</code> и <code>manager</code>.</p>
</section>

<!-- Редактирование книги -->
<div class="endpoint" id="bookcrossing-update">
    <h3>
        <span class="method method-put">PUT</span>
        /api/admin/bookcrossing/{id}
        <span class="badge badge-admin">Админ/Менеджер</span>
    </h3>
    <div class="description">
        Редактирование информации о книге
    </div>
    <div class="url">
        PUT https://ivybook.ru/api/admin/bookcrossing/1
    </div>
    <div class="parameters">
        <h4>📥 Параметры запроса (JSON)</h4>
        <div class="code-block">
{
    "title": "Новое название",
    "author": "Новый автор",
    "description": "Обновленное описание",
    "status": "available",
    "location": "Новое местоположение"
}
        </div>
    </div>
</div>

<!-- Удаление книги -->
<div class="endpoint" id="bookcrossing-delete">
    <h3>
        <span class="method method-delete">DELETE</span>
        /api/admin/bookcrossing/{id}
        <span class="badge badge-admin">Админ/Менеджер</span>
    </h3>
    <div class="description">
        Удаление книги из буккроссинга
    </div>
    <div class="url">
        DELETE https://ivybook.ru/api/admin/bookcrossing/1
    </div>
    <div class="response">
        <div class="code-block">
{
    "success": true,
    "message": "Книга удалена из буккроссинга"
}
        </div>
    </div>
</div>

<!-- Принудительный возврат -->
<div class="endpoint" id="bookcrossing-force">
    <h3>
        <span class="method method-post">POST</span>
        /api/admin/bookcrossing/{id}/force-return
        <span class="badge badge-admin">Админ/Менеджер</span>
    </h3>
    <div class="description">
        Принудительный возврат книги (без участия пользователя)
    </div>
    <div class="url">
        POST https://ivybook.ru/api/admin/bookcrossing/1/force-return
    </div>
</div>

<!-- Правила буккроссинга -->
<div class="endpoint">
    <h3>📋 Правила буккроссинга</h3>
    <div class="description">
        <ul>
            <li><strong>Добавление книг:</strong> любой авторизованный пользователь может добавить книгу</li>
            <li><strong>Взятие книг:</strong> можно взять любую доступную книгу, кроме своей</li>
            <li><strong>Возврат книг:</strong> после прочтения книгу нужно вернуть</li>
            <li><strong>Ответственность:</strong> пользователь отвечает за сохранность взятой книги</li>
            <li><strong>Модерация:</strong> администраторы могут редактировать и удалять любые книги</li>
        </ul>
    </div>
</div>
        <footer>
            <p>IvyBook API v1.0 | Авторизация через сессии Laravel</p>
            <p>Для тестирования используйте Postman с включенной отправкой cookies</p>
        </footer>
    </div>
</body>
</html>