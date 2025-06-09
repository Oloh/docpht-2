<?php

namespace DocPHT\Form;

use DocPHT\Core\Translator\T;
use Nette\Forms\Form;

class LostPasswordForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        $form->addGroup('Lost Password');

        $form->addEmail('username', 'Your email address:')
            ->setHtmlAttribute('placeholder', 'Enter your email address')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setRequired('Please enter your email address.');

        $form->addSubmit('submit', 'Send recovery email');

        return $form;
    }
}