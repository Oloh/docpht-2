<?php

namespace DocPHT\Controller;

use DocPHT\Core\Controller\BaseController;
use DocPHT\Core\Http\Session;
use DocPHT\Form\LoginForm;
use DocPHT\Form\LostPasswordForm;
use DocPHT\Form\RecoveryPasswordForm;
use DocPHT\Model\AccessLogModel;
use DocPHT\Model\AdminModel;
use Nette\Forms\Form;
use Nette\Mail\Message;
use System\Request;

class LoginController extends BaseController
{
    private object $adminModel;
    private object $accessLogModel;

    public function __construct(Session $session, Request $request)
    {
        parent::__construct($session, $request);
        $this->adminModel = new AdminModel();
        $this->accessLogModel = new AccessLogModel();
    }
    
    public function login()
    {
        if ($this->session->get('Active')) {
            header("Location: " . BASE_URL);
            exit;
        }

        $loginForm = new LoginForm();
        $form = $loginForm->create();
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];

        $this->view->load('Login', 'login/login.php', ['form' => $form]);
    }

    public function loginFormSucceeded(Form $form, \stdClass $values): void
    {
        $isLoggedIn = $this->checkLogin($values->username, $values->password);

        if ($isLoggedIn) {
            $this->msg->success('Welcome back!');
            header('Location: ' . BASE_URL);
            exit;
        } else {
            $this->msg->error('Invalid username or password.');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    public function checkLogin(string $username, string $password): bool
    {
        $user = $this->adminModel->getUserByUsername($username);
        
        if ($user && password_verify($password, $user['Password'])) {
            session_regenerate_id(true);
            $this->session->set('Username', $user['Username']);
            $this->session->set('Active', true);
            $this->accessLogModel->create($username);
            return true;
        }

        if ($user) {
            $this->accessLogModel->create($username, 'failed_login');
        }
        return false;
    }

    public function logout()
    { 
        $this->session->destroy();
        header("Location: " . BASE_URL);
        exit;
    }

    public function lostPassword()
    {
        $lostPasswordForm = new LostPasswordForm();
        $form = $lostPasswordForm->create();
        $form->onSuccess[] = [$this, 'lostPasswordFormSucceeded'];
        $this->view->load('Lost Password', 'login/lost_password.php', ['form' => $form]);
    }

    public function lostPasswordFormSucceeded(Form $form, \stdClass $values): void
    {
        $user = $this->adminModel->getUserByUsername($values->username);
        if (!$user) {
            $this->msg->error('No user found with that email address.');
            header('Location: ' . BASE_URL . 'lost-password');
            exit;
        }

        $token = bin2hex(random_bytes(32));
        $this->adminModel->setRecoveryToken($values->username, $token);

        $recoveryLink = BASE_URL . 'recovery/' . $token;

        $mail = new Message;
        $mail->setFrom(EMAIL)
            ->addTo($values->username)
            ->setSubject('Password Recovery')
            ->setBody("Click the following link to recover your password: \n" . $recoveryLink);

        try {
            $this->mailer->send($mail);
            $this->msg->info('A password recovery link has been sent to your email address.');
        } catch (\Nette\Mail\SendException $e) {
            $this->msg->error('There was an error sending the recovery email. Please contact support.');
        }

        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    
    public function recoveryPassword(string $token)
    {
        $user = $this->adminModel->getUserByToken($token);

        if (!$user || (time() - $user['recovery_time']) > 3600) { // 1 hour expiration
            $this->msg->error('This recovery link is invalid or has expired.');
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $recoveryPasswordForm = new RecoveryPasswordForm();
        $form = $recoveryPasswordForm->create();
        $form->onSuccess[] = function (Form $form, \stdClass $values) use ($token) {
            $this->recoveryPasswordFormSucceeded($values, $token);
        };
        
        $this->view->load('Recovery Password', 'login/lost_password.php', ['form' => $form]);
    }

    public function recoveryPasswordFormSucceeded(\stdClass $values, string $token): void
    {
        $hashedPassword = password_hash($values->password, PASSWORD_DEFAULT);
        $success = $this->adminModel->updatePasswordByToken($token, $hashedPassword);

        if ($success) {
            $this->msg->success('Your password has been updated successfully. Please log in.');
        } else {
            $this->msg->error('Failed to update password. Please try again.');
        }
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}