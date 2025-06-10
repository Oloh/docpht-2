<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Controller\BaseController;
use App\Forms\RecoveryPasswordForm;

class LoginController extends BaseController
{
    public function login()
    {
        $form = $this->loginForm->create();
        $this->view->load('Login', 'login/login.php', ['form' => $form]);
    }

    public function logout()
    {
        $this->accessLogModel->add(date('Y-m-d H:i:s'), $_SESSION['Username'], 'Logged out');
        $this->session->destroy();
        header('Location: ' . BASE_URL . 'login');
        exit();
    }

    public function lostPassword()
    {
        $form = $this->lostPasswordForm->create();
        $this->view->load('Lost Password', 'login/lost_password.php', ['form' => $form]);
    }

    public function recoveryPassword(array $vars)
    {
        $tokenData = $this->adminModel->getPasswordRecovery($vars['id']);

        if (!$tokenData) {
            $this->flasher?->addError('This recovery link is invalid or has expired.');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Create the form here, now that we have the token
        $this->recoveryPasswordForm = new RecoveryPasswordForm($tokenData);
        $form = $this->recoveryPasswordForm->create();
        
        $this->view->load('Recovery Password', 'login/recovery_password.php', [
            'form' => $form,
            'token' => $vars['id']
        ]);
    }
}