<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;

class CreatePageForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        
        $form->addGroup(T::trans('Create new page'));

        $form->addText('title', T::trans('Title:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter a title for the page'))
            ->setRequired(T::trans('Enter a title.'));

        $form->addTextArea('description', T::trans('Description:'))
            ->setHtmlAttribute('rows', 4)
            ->setHtmlAttribute('placeholder', T::trans('Enter a description for the page'))
            ->setRequired(T::trans('Enter a description.'));

        $form->addProtection(T::trans('Security token has expired, please submit the form again'));

        $form->addSubmit('submit', T::trans('Create page'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            $this->pageModel->createPage($values->title, $values->description);
            $this->flasher?->addSuccess(T::trans('Page created successfully.'));
            header('Location: ' . BASE_URL);
            exit;
        }
        
        return $form;
    }
}