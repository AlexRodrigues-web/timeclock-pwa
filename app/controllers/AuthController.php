<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\PasswordReset;
use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleOauth2;

class AuthController
{
    private function buildGoogleClient(): GoogleClient
    {
        $clientId = env_value('GOOGLE_CLIENT_ID', '');
        $clientSecret = env_value('GOOGLE_CLIENT_SECRET', '');
        $redirectUri = env_value('GOOGLE_REDIRECT_URI', '');

        if ($clientId === '' || $clientSecret === '' || $redirectUri === '') {
            throw new \RuntimeException('Google OAuth não configurado no .env.');
        }

        $client = new GoogleClient();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->setAccessType('online');
        $client->setPrompt('select_account');
        $client->setScopes(['openid', 'email', 'profile']);

        return $client;
    }

    public function loginForm(): void
    {
        View::render('auth/login', [
            'title' => 'Entrar',
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
            'old' => $_SESSION['old'] ?? [],
            'googleEnabled' => env_value('GOOGLE_CLIENT_ID', '') !== '' && env_value('GOOGLE_CLIENT_SECRET', '') !== '',
        ]);

        unset($_SESSION['error'], $_SESSION['success'], $_SESSION['old']);
    }

    public function login(): void
    {
        require BASE_PATH . '/app/models/User.php';

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $_SESSION['old'] = ['email' => $email];

        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Preenche email e password.';
            redirect_to('/auditor-app/public/login');
        }

        $user = User::findByEmail($email);

        if (!$user || (int) $user['active'] !== 1 || !password_verify($password, $user['password_hash'])) {
            $_SESSION['error'] = 'Credenciais inválidas.';
            redirect_to('/auditor-app/public/login');
        }

        User::touchLastLogin((int) $user['id']);

        Auth::login([
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ]);

        unset($_SESSION['old']);
        redirect_to('/auditor-app/public/');
    }

    public function registerForm(): void
    {
        View::render('auth/register', [
            'title' => 'Criar conta',
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
            'old' => $_SESSION['old'] ?? [],
            'googleEnabled' => env_value('GOOGLE_CLIENT_ID', '') !== '' && env_value('GOOGLE_CLIENT_SECRET', '') !== '',
        ]);

        unset($_SESSION['error'], $_SESSION['success'], $_SESSION['old']);
    }

    public function register(): void
    {
        require BASE_PATH . '/app/models/User.php';

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $passwordConfirm = trim($_POST['password_confirm'] ?? '');

        $_SESSION['old'] = [
            'name' => $name,
            'email' => $email,
        ];

        if ($name === '' || $email === '' || $password === '' || $passwordConfirm === '') {
            $_SESSION['error'] = 'Preenche todos os campos.';
            redirect_to('/auditor-app/public/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email inválido.';
            redirect_to('/auditor-app/public/register');
        }

        if (mb_strlen($name) < 3) {
            $_SESSION['error'] = 'O nome deve ter pelo menos 3 caracteres.';
            redirect_to('/auditor-app/public/register');
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'A password deve ter pelo menos 6 caracteres.';
            redirect_to('/auditor-app/public/register');
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'As passwords não coincidem.';
            redirect_to('/auditor-app/public/register');
        }

        if (User::emailExists($email)) {
            $_SESSION['error'] = 'Já existe um utilizador com esse email.';
            redirect_to('/auditor-app/public/register');
        }

        $userId = User::create($name, $email, password_hash($password, PASSWORD_DEFAULT));

        Auth::login([
            'id' => $userId,
            'name' => $name,
            'email' => $email,
        ]);

        unset($_SESSION['old']);
        $_SESSION['success'] = 'Conta criada com sucesso.';
        redirect_to('/auditor-app/public/account');
    }

    public function googleRedirect(): void
    {
        try {
            $client = $this->buildGoogleClient();
            $state = bin2hex(random_bytes(16));
            $_SESSION['google_oauth_state'] = $state;
            $client->setState($state);

            $authUrl = $client->createAuthUrl();
            redirect_to($authUrl);
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Google login ainda não está configurado.';
            redirect_to('/auditor-app/public/login');
        }
    }

    public function googleCallback(): void
    {
        require BASE_PATH . '/app/models/User.php';

        $state = trim($_GET['state'] ?? '');
        $code = trim($_GET['code'] ?? '');

        if ($state === '' || empty($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], $state)) {
            unset($_SESSION['google_oauth_state']);
            $_SESSION['error'] = 'Estado OAuth inválido.';
            redirect_to('/auditor-app/public/login');
        }

        unset($_SESSION['google_oauth_state']);

        if ($code === '') {
            $_SESSION['error'] = 'Código Google OAuth em falta.';
            redirect_to('/auditor-app/public/login');
        }

        try {
            $client = $this->buildGoogleClient();
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (!empty($token['error'])) {
                $_SESSION['error'] = 'Falha ao autenticar com Google.';
                redirect_to('/auditor-app/public/login');
            }

            $client->setAccessToken($token);

            $oauth2 = new GoogleOauth2($client);
            $googleUser = $oauth2->userinfo->get();

            $googleId = trim((string) $googleUser->id);
            $email = trim((string) $googleUser->email);
            $name = trim((string) $googleUser->name);
            $avatar = trim((string) $googleUser->picture);

            if ($googleId === '' || $email === '') {
                $_SESSION['error'] = 'Google não devolveu dados suficientes.';
                redirect_to('/auditor-app/public/login');
            }

            $user = User::findByGoogleId($googleId);

            if (!$user) {
                $user = User::findByEmail($email);

                if ($user) {
                    User::linkGoogleAccount((int) $user['id'], $googleId, $avatar !== '' ? $avatar : null);
                    User::updateGoogleProfile((int) $user['id'], $name !== '' ? $name : $user['name'], $avatar !== '' ? $avatar : null);
                    $user = User::findById((int) $user['id']);
                } else {
                    $newUserId = User::createGoogleUser(
                        $name !== '' ? $name : $email,
                        $email,
                        $googleId,
                        $avatar !== '' ? $avatar : null
                    );
                    $user = User::findById($newUserId);
                }
            } else {
                User::updateGoogleProfile((int) $user['id'], $name !== '' ? $name : $user['name'], $avatar !== '' ? $avatar : null);
                $user = User::findById((int) $user['id']);
            }

            if (!$user || (int) $user['active'] !== 1) {
                $_SESSION['error'] = 'Conta Google indisponível ou inativa.';
                redirect_to('/auditor-app/public/login');
            }

            User::touchLastLogin((int) $user['id']);

            Auth::login([
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            ]);

            $_SESSION['success'] = 'Sessão iniciada com Google.';
            redirect_to('/auditor-app/public/');
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Erro ao concluir login Google: ' . $e->getMessage();
            redirect_to('/auditor-app/public/login');
        }
    }

    public function forgotPasswordForm(): void
    {
        View::render('auth/forgot_password', [
            'title' => 'Recuperar password',
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
            'old' => $_SESSION['old'] ?? [],
            'resetLink' => $_SESSION['reset_link'] ?? null,
        ]);

        unset($_SESSION['error'], $_SESSION['success'], $_SESSION['old'], $_SESSION['reset_link']);
    }

    public function forgotPassword(): void
    {
        require BASE_PATH . '/app/models/User.php';
        require BASE_PATH . '/app/models/PasswordReset.php';

        $email = trim($_POST['email'] ?? '');
        $_SESSION['old'] = ['email' => $email];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Indica um email válido.';
            redirect_to('/auditor-app/public/forgot-password');
        }

        $user = User::findByEmail($email);

        if (!$user || (int) $user['active'] !== 1) {
            $_SESSION['success'] = 'Se o email existir, o link de recuperação foi preparado.';
            redirect_to('/auditor-app/public/forgot-password');
        }

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = password_hash($plainToken, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        PasswordReset::create((int) $user['id'], $tokenHash, $expiresAt);

        $_SESSION['success'] = 'Link de recuperação gerado.';
        $_SESSION['reset_link'] = '/auditor-app/public/reset-password?token=' . urlencode($plainToken);

        redirect_to('/auditor-app/public/forgot-password');
    }

    public function resetPasswordForm(): void
    {
        $token = trim($_GET['token'] ?? '');

        View::render('auth/reset_password', [
            'title' => 'Redefinir password',
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
            'token' => $token,
        ]);

        unset($_SESSION['error'], $_SESSION['success']);
    }

    public function resetPassword(): void
    {
        require BASE_PATH . '/app/models/User.php';
        require BASE_PATH . '/app/models/PasswordReset.php';

        $token = trim($_POST['token'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $passwordConfirm = trim($_POST['password_confirm'] ?? '');

        if ($token === '') {
            $_SESSION['error'] = 'Token inválido.';
            redirect_to('/auditor-app/public/forgot-password');
        }

        if ($password === '' || $passwordConfirm === '') {
            $_SESSION['error'] = 'Preenche os campos de password.';
            redirect_to('/auditor-app/public/reset-password?token=' . urlencode($token));
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'A password deve ter pelo menos 6 caracteres.';
            redirect_to('/auditor-app/public/reset-password?token=' . urlencode($token));
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'As passwords não coincidem.';
            redirect_to('/auditor-app/public/reset-password?token=' . urlencode($token));
        }

        $reset = PasswordReset::findValidByToken($token);

        if (!$reset) {
            $_SESSION['error'] = 'Link inválido ou expirado.';
            redirect_to('/auditor-app/public/forgot-password');
        }

        User::updatePassword((int) $reset['user_id'], password_hash($password, PASSWORD_DEFAULT));
        PasswordReset::markUsed((int) $reset['id']);

        $_SESSION['success'] = 'Password atualizada. Já podes entrar.';
        redirect_to('/auditor-app/public/login');
    }

    public function account(): void
    {
        Auth::requireAuth();

        require BASE_PATH . '/app/models/User.php';

        $user = User::findById((int) Auth::user()['id']);

        View::render('auth/account', [
            'title' => 'Minha conta',
            'user' => Auth::user(),
            'profile' => $user,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
        ]);

        unset($_SESSION['error'], $_SESSION['success']);
    }

    public function updateAccount(): void
    {
        Auth::requireAuth();

        require BASE_PATH . '/app/models/User.php';

        $currentUser = Auth::user();
        $id = (int) $currentUser['id'];

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currentPassword = trim($_POST['current_password'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $newPasswordConfirm = trim($_POST['new_password_confirm'] ?? '');

        if ($name === '' || $email === '') {
            $_SESSION['error'] = 'Nome e email são obrigatórios.';
            redirect_to('/auditor-app/public/account');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email inválido.';
            redirect_to('/auditor-app/public/account');
        }

        if (User::emailExists($email, $id)) {
            $_SESSION['error'] = 'Esse email já está em uso.';
            redirect_to('/auditor-app/public/account');
        }

        User::updateProfile($id, $name, $email);

        if ($newPassword !== '' || $newPasswordConfirm !== '' || $currentPassword !== '') {
            $dbUser = User::findById($id);

            if (!$dbUser || !password_verify($currentPassword, $dbUser['password_hash'])) {
                $_SESSION['error'] = 'Password atual inválida.';
                redirect_to('/auditor-app/public/account');
            }

            if (strlen($newPassword) < 6) {
                $_SESSION['error'] = 'A nova password deve ter pelo menos 6 caracteres.';
                redirect_to('/auditor-app/public/account');
            }

            if ($newPassword !== $newPasswordConfirm) {
                $_SESSION['error'] = 'A confirmação da nova password não coincide.';
                redirect_to('/auditor-app/public/account');
            }

            User::updatePassword($id, password_hash($newPassword, PASSWORD_DEFAULT));
        }

        Auth::login([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);

        $_SESSION['success'] = 'Conta atualizada com sucesso.';
        redirect_to('/auditor-app/public/account');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect_to('/auditor-app/public/login');
    }
}
