<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model\PageModel;
use App\Model\AdminModel;
use App\Model\HomePageModel;
use App\Model\VersionModel;
use App\Model\BackupsModel;
use App\Lib\DocBuilder;
use Flasher\Prime\FlasherInterface;
use Nette\Forms\Form;
use Nette\Utils\Html;

class MakeupForm
{
    private PageModel $pageModel;
    private HomePageModel $homePageModel;
    private AdminModel $adminModel;
    private VersionModel $versionModel;
    private BackupsModel $backupsModel;
    private DocBuilder $doc;
    public ?FlasherInterface $flasher;
    
    public function __construct()
    {
        $this->pageModel = new PageModel();
        $this->homePageModel = new HomePageModel();
        $this->adminModel = new AdminModel();
        $this->versionModel = new VersionModel();
        $this->backupsModel = new BackupsModel();
        $this->doc = new DocBuilder();
        
        if (isset($GLOBALS['flasher'])) {
            $this->flasher = $GLOBALS['flasher'];
        } else {
            $this->flasher = null;
        }
    }

    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];
        
        $form->addSelect('theme_selector', 'Theme Selector:', [
                'dark' => 'Dark',
                'light' => 'Light'
            ])
            ->setHtmlAttribute('onChange', 'this.form.submit()')
            ->setDefaultValue($this->adminModel->getTheme());
            
        $form->addSubmit('submit', 'Switch');

        if ($form->isSuccess()) {
            $values = $form->getValues();
            $this->adminModel->setTheme($values->theme_selector);
            $this->flasher->addSuccess('Data successfully updated.');
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        return $form;
    }

    public function bootstrap4(Form $form): void
    {
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class="form-group row"';
        $renderer->wrappers['pair']['.error'] = 'has-danger';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 col-form-label"';
        $renderer->wrappers['control']['description'] = 'span class=form-text';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=form-control-feedback';
        $renderer->wrappers['control']['.error'] = 'is-invalid';

        foreach ($form->getControls() as $control) {
            $type = $control->getOption('type');
            if ($type === 'button') {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-secondary');
                $usedPrimary = true;
            } elseif (in_array($type, ['text', 'textarea', 'select'], true)) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif ($type === 'file') {
                $control->getControlPrototype()->addClass('form-control-file');
            } elseif (in_array($type, ['checkbox', 'radio'], true)) {
                if ($control instanceof Nette\Forms\Controls\Checkbox) {
                    $control->getLabelPrototype()->addClass('form-check-label');
                } else {
                    $control->getItemLabelPrototype()->addClass('form-check-label');
                }
                $control->getControlPrototype()->addClass('form-check-input');
                $control->getSeparatorPrototype()->setName('div')->addClass('form-check');
            }
        }
    }
}