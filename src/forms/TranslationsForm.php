<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;

class TranslationsForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        
        $form->addGroup(T::trans('Translations'));

        $translations = json_decode(file_get_contents(realpath(__DIR__ . '/../Translations/code-translations.json')), true);
        asort($translations);
        
        $form->addSelect('language', T::trans('Language:'), $translations)
            ->setPrompt(T::trans('Select an option'))
            ->setHtmlAttribute('data-live-search', 'true')
            ->setDefaultValue($this->adminModel->getLanguage())
            ->setRequired(T::trans('Select an option'));

        $form->addProtection(T::trans('Security token has expired, please submit the form again'));

        $form->addSubmit('submit', T::trans('Save'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            $this->adminModel->setLanguage($values->language);
            $this->flasher?->addSuccess(T::trans('Data successfully updated.'));
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        return $form;
    }
}