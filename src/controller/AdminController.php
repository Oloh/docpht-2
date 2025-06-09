<?php

namespace DocPHT\Controller;

use DocPHT\Core\Controller\BaseController;
use DocPHT\Core\Http\Session;
use DocPHT\Core\NewAppVersion;
use DocPHT\Form\AddUserForm;
use DocPHT\Form\BackupsForms;
use DocPHT\Form\RemoveUserForm;
use DocPHT\Form\TranslationsForm;
use DocPHT\Form\UpdateEmailForm;
use DocPHT\Form\UpdatePasswordForm;
use DocPHT\Form\UploadLogoForm;
use DocPHT\Model\AccessLogModel;
use System\Request;

class AdminController extends BaseController
{
    private object $newAppVersion;
    private object $updatePasswordForm;
    private object $updateEmailForm;
    private object $removeUserForm;
    private object $addUserForm;
    private object $backupsForms;
    private object $translationsForm;
    private object $uploadLogoForm;
    private object $accessLogModel;

    public function __construct(Session $session, Request $request)
    {
        parent::__construct($session, $request);

        // Instantiate all dependencies
        $this->newAppVersion = new NewAppVersion();
        $this->updatePasswordForm = new UpdatePasswordForm($this);
        $this->updateEmailForm = new UpdateEmailForm($this);
        $this->removeUserForm = new RemoveUserForm($this);
        $this->addUserForm = new AddUserForm($this);
        $this->backupsForms = new BackupsForms($this);
        $this->translationsForm = new TranslationsForm($this);
        $this->uploadLogoForm = new UploadLogoForm($this);
        $this->accessLogModel = new AccessLogModel();
    }

	public function settings()
	{
		$newAppVersion = $this->newAppVersion->check();
		$this->view->load('Admin','admin/settings.php', ['newAppVersion' => $newAppVersion]);
	}

	public function updatePassword()
	{
		$form = $this->updatePasswordForm->create();
		$this->view->load('Update Password','admin/update_password.php', ['form' => $form]);
	}

	public function updateEmail()
	{
		$form = $this->updateEmailForm->create();
		$this->view->load('Update Email','admin/update_email.php', ['form' => $form]);
	}

	public function removeUser()
	{
		$form = $this->removeUserForm->create();
		$this->view->load('Remove User','admin/remove_user.php', ['form' => $form]);
	}
		
	public function addUser()
	{
		$form = $this->addUserForm->create();
		$this->view->load('Add user','admin/add_user.php', ['form' => $form]);
	}

	public function backup()
	{
		$this->view->load('Backups','admin/backups.php');
	}

	public function saveBackup()
	{
		$this->backupsForms->save();
	}

	public function restoreOptions()
	{
		$form = $this->backupsForms->restoreOptions();
		$this->view->load('Restore options','admin/restore_options.php', ['form' => $form]);

	}

	public function importBackup()
	{
		$form = $this->backupsForms->import();
		$this->view->load('Import a backup','admin/import_backup.php', ['form' => $form]);
	}

	public function exportBackup()
	{
		$this->backupsForms->export();
	}

	public function deleteBackup()
	{
		$this->backupsForms->delete();
	}

	public function translations()
	{
		$form = $this->translationsForm->create();
		$this->view->load('Translations','admin/translations.php', ['form' => $form]);
	}

	public function uploadLogo()
	{
		$logoForm = $this->uploadLogoForm->logo();
		$favForm = $this->uploadLogoForm->favicon();
		
		$this->view->load('Add logo','admin/upload_logo.php', [
			'logoForm' => $logoForm,
			'favForm' =>  $favForm
		]);
	}

	public function removeLogo()
	{
        if (file_exists('data/logo.png')) {
		    unlink('data/logo.png');
        }
		header('Location: ' . BASE_URL . 'admin/settings');
		exit;
	}

	public function removeFav()
	{
        if (file_exists('data/favicon.png')) {
		    unlink('data/favicon.png');
        }
		header('Location: ' . BASE_URL . 'admin/settings');
		exit;
	}

	public function lastLogin()
	{
		$userList = $this->accessLogModel->getUserList();
		$this->view->load('Last logins','admin/last_login.php', ['userList' => $userList]);
	}
}