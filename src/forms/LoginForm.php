<?php

namespace DocPHT\Form;

use DocPHT\Core\Translator\T;
use Nette\Forms\Form;

class LoginForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        $form->addGroup('Login to your account');

        $form->addEmail('username', 'Username (email):')
            ->setHtmlAttribute('placeholder', 'Enter your username')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setRequired('Enter your username');

        $form->addPassword('password', 'Password:')
            ->setHtmlAttribute('placeholder', 'Enter your password')
            ->setRequired('Enter your password');

        $form->addCheckbox('remember', 'Remember me on this device');
        $form->addSubmit('submit', 'Login');

        return $form;
    }
}