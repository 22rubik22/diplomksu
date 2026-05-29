<!DOCTYPE html>
<html>
<head>
    <title>Авторизация через Яндекс</title>
    <meta charset="utf-8">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f5f5f5;
        }
        .container {
            text-align: center;
        }
        .spinner {
            width: 40px;
            height: 40px;
            margin: 20px auto;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #FC3F1D;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
    (async function() {
        const success = {{ $success ? 'true' : 'false' }};
        const error = @json($error);
        const user = @json($user);
        
        function sendResult(type, data = {}) {
            if (window.opener) {
                window.opener.postMessage({
                    type: type,
                    ...data
                }, 'http://ivybook.ru');
                window.close();
            }
        }
        
        if (success && user) {
            sendResult('yandex_auth_success', { user });
        } else {
            sendResult('yandex_auth_error', { 
                message: error || 'Ошибка авторизации' 
            });
        }
    })();
    </script>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <p>Завершение авторизации...</p>
    </div>
</body>
</html>