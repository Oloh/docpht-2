<?php

namespace App\Core\Controller;

use DocPHT\Form\LoginForm;
use DocPHT\Form\SearchForm;
use DocPHT\Model\PageModel;
use DocPHT\Form\AddUserForm;
use DocPHT\Model\AdminModel;
use Instant\Core\Views\View;
use DocPHT\Core\Translator\T;
use DocPHT\Form\BackupsForms;
use DocPHT\Form\HomePageForm;
use DocPHT\Form\VersionForms;
use DocPHT\Model\SearchModel;
use DocPHT\Core\NewAppVersion;
use DocPHT\Model\BackupsModel;
use DocPHT\Model\VersionModel;
use DocPHT\Form\AddSectionForm;
use DocPHT\Form\CreatePageForm;
use DocPHT\Form\DeletePageForm;
use DocPHT\Form\RemoveUserForm;
use DocPHT\Form\UpdatePageForm;
use DocPHT\Form\UploadLogoForm;
use DocPHT\Model\HomePageModel;
use DocPHT\Core\Http\Session;
use DocPHT\Form\PublishPageForm;
use DocPHT\Form\SortSectionForm;
use DocPHT\Form\UpdateEmailForm;
use DocPHT\Model\AccessLogModel;
use DocPHT\Form\LostPasswordForm;
use DocPHT\Form\TranslationsForm;
use DocPHT\Form\InsertSectionForm;
use DocPHT\Form\ModifySectionForm;
use DocPHT\Form\RemoveSectionForm;
use DocPHT\Form\VersionSelectForm;
use DocPHT\Form\UpdatePasswordForm;
use DocPHT\Form\RecoveryPasswordForm;

class BaseController
{
    protected $view;
    protected $removeUserForm;
    protected $updatePasswordForm;
    // ... (all other protected properties) ...
    protected $session;
    protected $newAppVersion;
    // REMOVED: protected $msg;

    public function __construct()
    {
        $this->view = new View();
        $this->updatePasswordForm = new UpdatePasswordForm();
        // ... (all other instantiations) ...
        $this->session = new Session();
        $this->newAppVersion = new NewAppVersion();
        // REMOVED: The incorrect Flasher instantiation
    }

    public function search()
    {
        $this->searchModel->feed();
        $results = $this->search->create();
        if (isset($results)) {
            $this->view->load('Search','search_results.php', ['results' => $results]);
        } else {
            // Use the global flasher() function
            flasher()->info(T::trans('Search term did not produce results'));
            header('Location:'.$_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    public function switchTheme()
    {
        if (isset($_COOKIE["theme"]) && $_COOKIE["theme"] == 'dark') {
            setcookie("theme", "light");            
        } else {
            setcookie("theme", "dark");
        }
        header('Location:'.$_SERVER['HTTP_REFERER']);
        exit;
    }
}