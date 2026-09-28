<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function login(array $user): void
    {
        $_SESSION['user'] = $user;
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            $_SESSION['error'] = 'Precisas de entrar para continuar.';
            redirect_to('/auditor-app/public/login');
        }
    }
}
