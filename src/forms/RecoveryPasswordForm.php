<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Nette\Security\Passwords;

class RecoveryPasswordForm extends MakeupForm
{
    private Passwords $passwords;
    private array $token;

    public function __construct(array $token)
    {
        parent::__construct();
        $this->passwords = new Passwords();
        $this->token = $token;
    }

    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];

        $form->addGroup(T::trans('Recovery password for: ') . ($this->token['username'] ?? ''))
            ->setOption('description', T::trans('Enter a new password for the account.'));

        $form->addPassword('password', T::trans('Enter new password:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter new password'))
            ->addRule(Form::MIN_LENGTH, T::trans('The password must be at least 8 characters long.'), 8)
            ->setRequired(T::trans('Enter a password.'));
            
        $form->addPassword('password_confirm', T::trans('Confirm new password:'))
            ->setHtmlAttribute('placeholder', T::trans('Confirm new password'))
            ->addRule(Form::EQUAL, T::trans('Passwords do not match!'), $form['password'])
            ->setRequired(T::trans('Confirm new password.'));

        $form->addHidden('id', $this->token['id'] ?? '');

        $form->addProtection(T::trans('Security token has expired, please submit the form again'));

        $form->addSubmit('submit', T::trans('Update'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            $this->adminModel->updatePassword($this->token['username'], $this->passwords->hash($values->password));
            $this->adminModel->deletePasswordRecovery($this->token['token']);
            $this->flasher?->addSuccess(T::trans('Password changed successfully.'));
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        return $form;
    }
}