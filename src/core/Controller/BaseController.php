<?php

declare(strict_types=1);

namespace App\Core\Controller;

use App\Core\NewAppVersion;
use App\Core\Http\Session;
use App\Core\Views\View;
use App\Forms\AddSectionForm;
use App\Forms\AddUserForm;
use App\Forms\BackupsForms;
use App\Forms\CreatePageForm;
use App\Forms\DeletePageForm;
use App\Forms\HomePageForm;
use App\Forms\InsertSectionForm;
use App\Forms\LoginForm;
use App\Forms\LostPasswordForm;
use App\Forms\MakeupForm;
use App\Forms\ModifySectionForm;
use App\Forms\PublishPageForm;
use App\Forms\RecoveryPasswordForm;
use App\Forms\RemoveSectionForm;
use App\Forms\RemoveUserForm;
use App\Forms\SearchForm;
use App\Forms\SortSectionForm;
use App\Forms\TranslationsForm;
use App\Forms\UpdateEmailForm;
use App\Forms\UpdatePageForm;
use App\Forms\UpdatePasswordForm;
use App\Forms\UploadLogoForm;
use App\Forms\VersionForms;
use App\Forms\VersionSelectForm;
use App\Model\AccessLogModel;
use App\Model\AdminModel;
use App\Model\BackupsModel;
use App\Model\HomePageModel;
use App\Model\PageModel;
use App\Model\SearchModel;
use App\Model\VersionModel;

abstract class BaseController
{
    protected View $view;
    protected UpdatePasswordForm $updatePasswordForm;
    protected UpdateEmailForm $updateEmailForm;
    protected AddUserForm $addUserForm;
    protected RemoveUserForm $removeUserForm;
    protected BackupsForms $backupsForms;
    protected TranslationsForm $translationsForm;
    protected UploadLogoForm $uploadlogo;
    protected AccessLogModel $accessLogModel;
    protected LostPasswordForm $lostPasswordForm;
    protected ?RecoveryPasswordForm $recoveryPasswordForm = null; // Changed
    protected LoginForm $loginForm;
    protected HomePageModel $homePageModel;
    protected SearchForm $searchForm;
    protected SearchModel $searchModel;
    protected PageModel $pageModel;
    protected CreatePageForm $createPageForm;
    protected UpdatePageForm $updatePageForm;
    protected DeletePageForm $deletePageForm;
    protected AddSectionForm $addSectionForm;
    protected InsertSectionForm $insertSectionForm;
    protected ModifySectionForm $modifySectionForm;
    protected RemoveSectionForm $removeSectionForm;
    protected SortSectionForm $sortSectionForm;
    protected PublishPageForm $publishPageForm;
    protected VersionSelectForm $versionSelectForm;
    protected VersionForms $versionForms;
    protected AdminModel $settings;
    protected BackupsModel $backups;
    protected VersionModel $versionModel;
    protected MakeupForm $makeupForm;
    protected HomePageForm $homePageForm;
    protected Session $session;
    protected NewAppVersion $newAppVersion;

    public function __construct()
    {
        $this->view = new View();
        $this->updatePasswordForm = new UpdatePasswordForm();
        $this->updateEmailForm = new UpdateEmailForm();
        $this->addUserForm = new AddUserForm();
        $this->removeUserForm = new RemoveUserForm();
        $this->backupsForms = new BackupsForms();
        $this->translationsForm = new TranslationsForm();
        $this->uploadlogo = new UploadLogoForm();
        $this->accessLogModel = new AccessLogModel();
        $this->lostPasswordForm = new LostPasswordForm();
        // $this->recoveryPasswordForm is now created in LoginController
        $this->loginForm = new LoginForm();
        $this->homePageModel = new HomePageModel();
        $this->searchForm = new SearchForm();
        $this->searchModel = new SearchModel();
        $this->pageModel = new PageModel();
        $this->createPageForm = new CreatePageForm();
        $this->updatePageForm = new UpdatePageForm();
        $this->deletePageForm = new DeletePageForm();
        $this->addSectionForm = new AddSectionForm();
        $this->insertSectionForm = new InsertSectionForm();
        $this->modifySectionForm = new ModifySectionForm();
        $this->removeSectionForm = new RemoveSectionForm();
        $this->sortSectionForm = new SortSectionForm();
        $this->publishPageForm = new PublishPageForm();
        $this->versionSelectForm = new VersionSelectForm();
        $this->versionForms = new VersionForms();
        $this->settings = new AdminModel();
        $this->backups = new BackupsModel();
        $this->versionModel = new VersionModel();
        $this->makeupForm = new MakeupForm();
        $this->homePageForm = new HomePageForm();
        $this->session = new Session();
        $this->newAppVersion = new NewAppVersion();
    }
}