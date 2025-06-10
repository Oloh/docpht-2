<?php

/**
 * This file is part of the DocPHT project.
 * * @author Valentino Pesce
 * @copyright (c) Valentino Pesce <valentino@iltuobrand.it>
 * @copyright (c) Craig Crosby <creecros@gmail.com>
 * * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;
use Nette\Utils\Html;

class RemoveUserForm extends MakeupForm
{

    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];

        $form->addGroup(T::trans('Remove a user'))
            ->setOption('description', T::trans('Select username for removal.'));
        
        $filterUsers = array_filter($this->adminModel->getUsernames(), function ($var) {
            return (isset($_SESSION['Username']) && strpos($var, $_SESSION['Username']) === false);
        });

        $form->addSelect('user',T::trans('Remove a user:'), $filterUsers)
            ->setPrompt(T::trans('Select a user'))
            ->setHtmlAttribute('data-live-search','true')
            ->setRequired(T::trans('Select a user'));
        
        $form->addProtection(T::trans('Security token has expired, please submit the form again'));
        
        $form->addSubmit('submit', T::trans('Remove user'))->setAttribute('onclick','return confirmationRemoval()');
        
        if ($form->isSuccess()) {
            $values = $form->getValues();
            if (isset($values->user)) {
                $this->adminModel->removeUser($values->user);
                // Use the flasher object that is inherited from the parent form
                $this->flasher?->addSuccess(T::trans('User successfully deleted'));
                header('Location: ' . BASE_URL . 'admin');
                exit;
            } else {
                $this->flasher?->addError(T::trans('Sorry something didn\'t work!'));
                header('Location: ' . BASE_URL . 'admin');
                exit;
            }
            
        }
        return $form;
    }
}