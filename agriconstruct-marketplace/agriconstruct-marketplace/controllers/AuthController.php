<?php

/**
 * ============================================================================
 * controllers/AuthController.php
 * ----------------------------------------------------------------------------
 * Inregistrare, autentificare, deconectare si gestionarea parolei.
 * Parolele sunt mereu stocate/verificate cu password_hash() / password_verify().
 *
 * Rute:
 *   POST /register
 *   POST /login
 *   POST /logout
 *   POST /password/forgot
 *   POST /password/reset
 *   PUT  /password/change   (necesita autentificare)
 * ============================================================================
 */

declare(strict_types=1);

final class AuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /** POST /register */
    public function register(): void
    {
        $data = $this->input();

        $validator = new Validator($data);
        $validator->required('first_name', 'nume')
            ->required('last_name', 'prenume')
            ->required('email')->email('email')
            ->required('password', 'parola')->minLength('password', 8)
            ->required('county', 'judet')
            ->required('city', 'oras');

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        if ($this->userModel->findByEmail($data['email']) !== null) {
            Response::validationError(['email' => ['Exista deja un cont cu aceasta adresa de email.']]);
        }

        $userId = $this->userModel->create([
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => strtolower(trim($data['email'])),
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => 'user',
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'],
            'county' => $data['county'],
        ]);

        $user = $this->userModel->findById($userId);
        Response::success(User::sanitize($user), 'Cont creat cu succes.', 201);
    }

    /** POST /login */
    public function login(): void
    {
        $data = $this->input();

        $validator = new Validator($data);
        $validator->required('email')->email('email')->required('password', 'parola');
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $user = $this->userModel->findByEmail(strtolower(trim($data['email'])));

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::error('Email sau parola incorecta.', 401);
        }

        if ($user['status'] === 'blocked') {
            Response::forbidden('Acest cont a fost blocat. Contacteaza administratorul.');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        Response::success(User::sanitize($user), 'Autentificare reusita.');
    }

    /** POST /logout */
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        Response::success(null, 'Deconectare reusita.');
    }

    /** POST /password/forgot */
    public function forgotPassword(): void
    {
        $data = $this->input();
        $validator = new Validator($data);
        $validator->required('email')->email('email');
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $user = $this->userModel->findByEmail(strtolower(trim($data['email'])));
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE users SET remember_token = :token WHERE id = :id');
            $stmt->execute(['token' => $token, 'id' => $user['id']]);
            // TODO: trimite $token pe email printr-un serviciu SMTP/API extern.
        }

        Response::success(null, 'Daca adresa exista in sistem, vei primi instructiuni de resetare.');
    }

    /** POST /password/reset */
    public function resetPassword(): void
    {
        $data = $this->input();
        $validator = new Validator($data);
        $validator->required('token')->required('password', 'parola')->minLength('password', 8);
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM users WHERE remember_token = :token LIMIT 1');
        $stmt->execute(['token' => $data['token']]);
        $userId = $stmt->fetchColumn();

        if (!$userId) {
            Response::error('Token invalid sau expirat.', 400);
        }

        $this->userModel->updatePassword((int) $userId, password_hash($data['password'], PASSWORD_BCRYPT));
        $clearStmt = $db->prepare('UPDATE users SET remember_token = NULL WHERE id = :id');
        $clearStmt->execute(['id' => $userId]);

        Response::success(null, 'Parola a fost schimbata cu succes.');
    }

    /** PUT /password/change */
    public function changePassword(): void
    {
        $user = AuthMiddleware::handle();
        $data = $this->input();

        $validator = new Validator($data);
        $validator->required('current_password', 'parola curenta')
            ->required('new_password', 'parola noua')->minLength('new_password', 8);
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $currentUser = $this->userModel->findById($user['id']);
        if (!password_verify($data['current_password'], $currentUser['password'])) {
            Response::error('Parola curenta este incorecta.', 400);
        }

        $this->userModel->updatePassword($user['id'], password_hash($data['new_password'], PASSWORD_BCRYPT));
        Response::success(null, 'Parola a fost actualizata.');
    }
}
