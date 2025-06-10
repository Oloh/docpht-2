<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;

class ModifySectionForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        
        $id = $_SESSION['page_id'] ?? null;
        $order = $_SESSION['section_id'] ?? null;
        $section = $this->pageModel->getSection($id, $order);

        $form->addGroup(T::trans('Modify section'));

        $form->addSelect('options', T::trans('Content Type:'), $this->doc->getOptions())
			->setPrompt(T::trans('Select an option'))
            ->setDefaultValue($section['key'] ?? '')
			->setRequired(T::trans('You must select a content type.'));
            
        $form->addTextArea('option_content', T::trans('Content:'))
            ->setHtmlAttribute('rows', 10)
            ->setHtmlAttribute('id', 'content')
            ->setDefaultValue($section['v1'] ?? '');

        $form->addSelect('language', T::trans('Code Language:'), $this->doc->listCodeLanguages())
            ->setPrompt(T::trans('Select an option'))
            ->setDefaultValue($section['v2'] ?? '');
        
        $form->addText('names', T::trans('Image Name:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter image name'))
            ->setDefaultValue($section['v2'] ?? '');

        $form->addText('trgs', T::trans('Button Link Target:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter button link target'))
            ->setDefaultValue($section['v3'] ?? '');

        $form->addUpload('file', T::trans('Choose a file to upload:'));
        
        $form->addProtection(T::trans('Security token has expired, please submit the form again'));

        $form->addSubmit('submit', T::trans('Modify section'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            $file_path = $this->doc->upload($values->file, $_SESSION['page_path']);
            $this->pageModel->modifySection(
                $order,
                $_SESSION['page_path'],
                $this->doc->valuesToArray($values, $file_path, $section)
            );
            $this->doc->removeOldFile($section['key'], $values->options, $section['v1']);
            $this->doc->buildPhpPage($id);
            $this->flasher?->addSuccess(T::trans('Section modified successfully.'));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        return $form;
    }
}