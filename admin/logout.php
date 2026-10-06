<?php
// 1. Запускаем сессию, чтобы получить доступ к её данным
session_start();

// 2. Полностью очищаем массив сессии
// Это удалит $_SESSION['admin_logged'], $_SESSION['admin_id'] и всё остальное
session_unset(); 

// 3. Уничтожаем сам файл сессии на сервере
session_destroy();

// 4. (Опционально, но рекомендуется) Удаляем куку сессии у клиента
// Параметры: имя куки, пустое значение, время в прошлом, путь (берем из настроек сессии)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Перенаправляем пользователя на страницу входа
header('Location: login.php');
exit;