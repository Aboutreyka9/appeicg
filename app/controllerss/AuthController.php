<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class AuthControllerss
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/auth/login
     */
    public function login(): void
    {
        
        $email    = Validator::post('email');
        $password = Validator::post('password');

        $v = new Validator();
        $v->required('email', $email, 'Email')
          ->email('email', $email, 'Email')
          ->required('password', $password, 'Mot de passe');

        if ($v->fails()) {
            Response::error('Données invalides.', 422, $v->errors());
        }

        $result = $this->authService->login($email, $password);

        if (!$result['success']) {
            Response::error($result['message'], 401);
        }

        Response::success($result['message'], $result['data'] ?? []);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(): void
    {
        $this->authService->logout();
        Response::success('Déconnexion réussie.');
    }

    /**
     * GET /api/auth/check
     */
    public function check(): void
    {
        if ($this->authService->isAuthenticated()) {
            $user = $this->authService->getSessionUser();
            Response::success('Authentifié.', $user);
        } else {
            Response::error('Non authentifié.', 401);
        }
    }
}
