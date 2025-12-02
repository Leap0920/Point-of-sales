<?php

require_once __DIR__ . '/Database.php';

class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(string $username, string $password): bool
    {
        self::startSession();

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT u.id, u.name, u.username, u.password_hash, u.is_active, r.name AS role_name 
                               FROM users u 
                               JOIN roles r ON u.role_id = r.id 
                               WHERE u.username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !$user['is_active']) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'username' => $user['username'],
            'role' => $user['role_name'],
        ];

        return true;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function user(): ?array
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: login.php');
            exit;
        }
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && $user['role'] === 'Admin';
    }
}


