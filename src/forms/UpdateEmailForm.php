<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;
use Nette\Utils\Html;

class UpdateEmailForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        
        $form->addGroup(T::trans('Update Email for: ') . ($_SESSION['Username'] ?? ''))
            ->setOption('description', T::trans('Enter a new email for the account.'));

        $form->addEmail('newemail', T::trans('Enter a new email address:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter a new email address'))
            ->setRequired(T::trans('Enter a new email address'));

        $form->addPassword('password', T::trans('Confirm your password:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter your password'))
            ->setRequired(T::trans('Enter your password'));

        $form->addProtection(T::trans('Security token has expired, please submit the form again'));
        
        $form->addSubmit('submit', T::trans('Update user email'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            if (isset($_SESSION['Username']) && $this->adminModel->verifyPassword($_SESSION['Username'], $values->password)) {
                $this->adminModel->updateEmail($values->newemail, $_SESSION['Username']);
                $this->flasher?->addSuccess(T::trans('User email updated successfully.'));
                header('Location: ' . BASE_URL . 'admin');
                exit;
            } else {
                $this->flasher?->addError(T::trans('Sorry, your password was not correct.'));
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }
        
        return $form;
    }
}