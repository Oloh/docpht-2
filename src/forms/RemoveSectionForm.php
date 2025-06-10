<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;

class RemoveSectionForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        
        $form->addGroup(T::trans('Remove section from this page'));

        $sections = $this->pageModel->getSections($_SESSION['page_id']);
        $form->addSelect('section_id', T::trans('Select a section to remove:'), $sections)
            ->setPrompt(T::trans('Select a section'))
            ->setRequired(T::trans('You must select a section.'));
            
        $form->addProtection(T::trans('Security token has expired, please submit the form again'));

        $form->addSubmit('submit', T::trans('Remove section'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            $this->pageModel->removeSection($values->section_id, $_SESSION['page_path']);
            $this->doc->buildPhpPage($_SESSION['page_id']);
            $this->flasher?->addSuccess(T::trans('Section removed successfully.'));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        return $form;
    }
}