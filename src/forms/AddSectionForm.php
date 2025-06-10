<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;

class AddSectionForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];

        $form->addGroup(T::trans('Add new section to this page'));

        $form->addSelect('options', T::trans('Content Type:'), $this->doc->getOptions())
			->setPrompt(T::trans('Select an option'))
			->setRequired(T::trans('You must select a content type.'));
            
        $form->addTextArea('option_content', T::trans('Content:'))
            ->setHtmlAttribute('rows', 10)
            ->setHtmlAttribute('id', 'content');

        $form->addSelect('language', T::trans('Code Language:'), $this->doc->listCodeLanguages())
            ->setPrompt(T::trans('Select an option'));
        
        $form->addText('names', T::trans('Image Name:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter image name'));

        $form->addText('trgs', T::trans('Button Link Target:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter button link target'));

        $form->addUpload('file', T::trans('Choose a file to upload:'));
        
        $form->addProtection(T::trans('Security token has expired, please submit the form again'));

        $form->addSubmit('submit', T::trans('Add section'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            $file_path = $this->doc->upload($values->file, $_SESSION['page_path']);
            $this->pageModel->addSection(
                $_SESSION['page_path'],
                $this->doc->valuesToArray($values, $file_path)
            );
            $this->doc->buildPhpPage($_SESSION['page_id']);
            $this->flasher?->addSuccess(T::trans('Section added successfully.'));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        return $form;
    }
}