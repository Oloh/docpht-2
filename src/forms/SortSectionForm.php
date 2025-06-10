<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;

class SortSectionForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        
        $form->addGroup(T::trans('Sort sections'));

        $sections = $this->pageModel->getSections($_SESSION['page_id']);
        $form->addText('sort', T::trans('Sort:'))
            ->setHtmlAttribute('id', 'sort')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('value', implode(',', array_keys($sections)))
            ->setRequired(T::trans('You must provide a sort order.'));
            
        $form->addProtection(T::trans('Security token has expired, please submit the form again'));

        $form->addSubmit('submit', T::trans('Sort sections'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            $this->pageModel->sortSections(
                $_SESSION['page_path'],
                explode(',', $values->sort)
            );
            $this->doc->buildPhpPage($_SESSION['page_id']);
            $this->flasher?->addSuccess(T::trans('Sections sorted successfully.'));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        return $form;
    }
}