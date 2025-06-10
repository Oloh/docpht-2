<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;

class LoginForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];

        $form->addEmail('username', T::trans('Username:'))
            ->setHtmlAttribute('placeholder', T::trans('Username'))
            ->setRequired(T::trans('Enter a username.'));
            
        $form->addPassword('password', T::trans('Password:'))
            ->setHtmlAttribute('placeholder', T::trans('Password'))
            ->setRequired(T::trans('Enter a password.'));
            
        $form->addCheckbox('remember', T::trans('Remember me on this computer'));
        
        $form->addProtection(T::trans('Security token has expired, please submit the form again'));

        $form->addSubmit('submit', T::trans('Sign in'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            if ($this->adminModel->login($values->username, $values->password)) {
                if ($values->remember) {
                    // Set a longer session lifetime if "Remember me" is checked
                    $this->session->setExpiration('+ 30 days');
                }
                $user = $this->adminModel->getUserData($values->username);
                $this->session->start();
                $_SESSION['Active'] = true;
                $_SESSION['Username'] = $user['username'];
                $_SESSION['Email'] = $user['email'];
                $_SESSION['Role'] = $user['role'];
                $_SESSION['Language'] = $user['language'];
                $this->accessLogModel->add(date('Y-m-d H:i:s'), $values->username, 'Logged in');
                header('Location: ' . BASE_URL);
                exit;
            } else {
                $this->flasher?->addError(T::trans('Incorrect username or password.'));
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }
        
        return $form;
    }
}