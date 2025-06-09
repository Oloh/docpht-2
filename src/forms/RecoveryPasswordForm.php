<?php

namespace DocPHT\Form;

use DocPHT\Core\Translator\T;
use Nette\Forms\Form;

class RecoveryPasswordForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        $form->addGroup('Recovery Password');

        $form->addPassword('password', 'New password:')
            ->setHtmlAttribute('placeholder', 'Enter a new password')
            ->setRequired('Please enter a new password.')
            ->addRule($form::MIN_LENGTH, 'The password must be at least %d characters long.', 8)
            ->addRule($form::PATTERN, 'The password must contain at least one number.', '.*[0-9].*');

        $form->addPassword('password_confirm', 'Confirm new password:')
            ->setHtmlAttribute('placeholder', 'Confirm the new password')
            ->setRequired('Please confirm your new password.')
            ->addRule($form::EQUAL, 'Passwords do not match.', $form['password']);

        $form->addSubmit('submit', 'Set new password');

        return $form;
    }
}