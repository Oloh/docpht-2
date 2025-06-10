<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;

class UpdatePageForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        
        $id = $_SESSION['page_id'] ?? null;
        $pageData = $this->pageModel->getPageData($id);

        $form->addGroup(T::trans('Edit page content'));

        $form->addText('title', T::trans('Title:'))
            ->setDefaultValue($pageData['title'] ?? '')
            ->setRequired(T::trans('Enter a title.'));

        $form->addTextArea('description', T::trans('Description:'))
            ->setHtmlAttribute('rows', 4)
            ->setDefaultValue($pageData['description'] ?? '')
            ->setRequired(T::trans('Enter a description.'));

        $form->addHidden('id', $id);
        
        $form->addProtection(T::trans('Security token has expired, please submit the form again'));

        $form->addSubmit('submit', T::trans('Update page'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            $this->pageModel->updatePage($values->id, $values->title, $values->description);
            $this->flasher?->addSuccess(T::trans('Page updated successfully.'));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        return $form;
    }
}