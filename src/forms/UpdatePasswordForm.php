<?php

/**
 * This file is part of the DocPHT project.
 * * @author Valentino Pesce
 * @copyright (c) Valentino Pesce <valentino@iltuobrand.it>
 * @copyright (c) Craig Crosby <creecros@gmail.com>
 * * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;
use Nette\Utils\Html;

class UpdatePasswordForm extends MakeupForm
{

    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];

        $form->addGroup(T::trans('Update Password for: ') . ($_SESSION['Username'] ?? ''))
            ->setOption('description', T::trans('Enter a new password for the account.'));

        $form->addPassword('oldpassword', T::trans('Confirm current password:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter current password'))
            ->setHtmlAttribute('autocomplete','off')
            ->setRequired(T::trans('Enter password'));

        $form->addGroup(T::trans('Randomized password'))
            ->setOption('description', Html::el('p')->setText($this->adminModel->randomPassword()));
            
        $form->addPassword('newpassword', T::trans('Enter new password:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter new password'))
            ->setHtmlAttribute('autocomplete','off')
            ->addRule(Form::MIN_LENGTH, T::trans('The password must be at least 8 characters long.'), 8)
            ->setRequired(T::trans('Confirm password'));
            
        $form->addPassword('confirmpassword', T::trans('Confirm new password:'))
            ->setHtmlAttribute('placeholder', T::trans('Confirm password'))
            ->setHtmlAttribute('autocomplete','off')
            ->addRule($form::EQUAL, T::trans('Passwords do not match!'), $form['newpassword'])
            ->setRequired(T::trans('Confirm password'));
        
        $form->addProtection(T::trans('Security token has expired, please submit the form again'));
        
        $form->addSubmit('submit',T::trans('Update user password'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            if (isset($_SESSION['Username']) && $this->adminModel->verifyPassword($_SESSION['Username'], $values->oldpassword)) {
                // The AdminModel handles the hashing, so we pass the plain password
                $this->adminModel->updatePassword($_SESSION['Username'], $values->newpassword);
                $this->flasher?->addSuccess(T::trans('User password updated successfully.'));
                header('Location: ' . BASE_URL . 'admin');
                exit;
            } else {
                $this->flasher?->addError(T::trans('Sorry, your current password was not correct.'));
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }
        
        return $form;
    }
}