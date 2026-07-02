<?php

/**
 * ============================================================================
 * controllers/UserController.php
 * ----------------------------------------------------------------------------
 * Gestionarea profilului utilizatorului autentificat.
 *
 * Rute:
 *   GET /users/profile
 *   PUT /users/profile
 * ============================================================================
 */

declare(strict_types=1);

final class UserController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function profile(): void
    {
        $authUser = AuthMiddleware::handle();
        $user = $this->userModel->findById($authUser['id']);

        if (!$user) {
            Response::notFound('Utilizatorul nu a fost gasit.');
        }

        Response::success(User::sanitize($user));
    }

    public function updateProfile(): void
    {
        $authUser = AuthMiddleware::handle();
        $data = $this->input();

        $validator = new Validator($data);
        $validator->required('first_name', 'nume')->required('last_name', 'prenume');
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $this->userModel->updateProfile($authUser['id'], $data);
        $updated = $this->userModel->findById($authUser['id']);

        Response::success(User::sanitize($updated), 'Profil actualizat cu succes.');
    }
}
