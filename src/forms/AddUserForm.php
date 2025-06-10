<?php

/**
 * This file is part of the DocPHT project.
 * * @author Valentino Pesce
 * @copyright (c) Valentino Pesce <valentino@iltuobrand.it>
 * @copyright (c) Craig Crosby <creecros@gmail.com>
 * * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Latte\Engine;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use Nette\Mail\SendmailMailer;

class AddUserForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $form->onRender[] = [$this, 'bootstrap4'];

        $form->addGroup(T::trans('Add user'))
            ->setOption('description', T::trans('Enter a new email and password for the account.'));

        $form->addEmail('username', T::trans('Enter email:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter email'))
            ->setHtmlAttribute('autocomplete','off')
            ->setRequired(T::trans('Enter email'));
        
        $form->addGroup(T::trans('Randomized password'))
            ->setOption('description', Html::el('p')->setText($this->adminModel->randomPassword()));

        $form->addPassword('password', T::trans('Enter password:'))
            ->setHtmlAttribute('placeholder', T::trans('Enter password'))
            ->setHtmlAttribute('autocomplete','off')
            ->addRule(Form::MIN_LENGTH, T::trans('The password must be at least 8 characters long.'), 8)
            ->setRequired(T::trans('Enter password'));
            
        $form->addPassword('confirmpassword', T::trans('Confirm password:'))
            ->setHtmlAttribute('placeholder', T::trans('Confirm password'))
            ->setHtmlAttribute('autocomplete','off')
            ->addRule($form::EQUAL, T::trans('Passwords do not match!'), $form['password'])
            ->setRequired(T::trans('Confirm password'));
        
        $form->addCheckbox('admin', T::trans('Add administrator privileges?'));

        $translations = json_decode(file_get_contents(realpath(__DIR__ . '/../Translations/code-translations.json')), true);
        asort($translations);
        $form->addSelect('translations',T::trans('Language:'), $translations)
            ->setPrompt(T::trans('Select an option'))
            ->setHtmlAttribute('data-live-search','true')
            ->setRequired(T::trans('Select an option'));
        
        $form->addProtection(T::trans('Security token has expired, please submit the form again'));
        
        $form->addSubmit('submit',T::trans('Add new user'));

        if ($form->isSuccess()) {
            $values = $form->getValues();
            if (in_array($values->username, $this->adminModel->getUsernames())) {
                $this->flasher?->addError(T::trans('This username %username% is in use!', ['%username%' => $values->username]));
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            if (isset($values->username) && isset($values->password) && $values->password === $values->confirmpassword) {
                $this->adminModel->create($values);

                $latte = new Engine;
                $params = [
                    'BASE_URL' => BASE_URL,
                    'title' => 'You now have a new account',
                    'password' => $values->password,
                    'content' => 'Sign in now and start adding your content.'
                ]; 

                $mail = new Message;
                $mail->setFrom('no-reply@'.DOMAIN_NAME)
                    ->addTo($values->username)
                    ->setSubject('New account '.DOMAIN_NAME)
                    ->setHtmlBody($latte->renderToString(__DIR__ . '/../Views/email/new_account.latte', $params));

                if (defined('SMTPMAILER') && SMTPMAILER === true) {
                    $mailer = new SmtpMailer([
                        'host' => SMTPHOST,
                        'port' => SMTPPORT,
                        'username' => SMTPUSERNAME,
                        'password' => SMTPPASSWORD,
                        'secure' => SMTPENCRYPT,
                    ]);
                    $mailer->send($mail);
                } else {
                    $mailer = new SendmailMailer;
                    $mailer->send($mail);
                }
                $this->flasher?->addSuccess(T::trans('User created successfully.'));
                header('Location:'.BASE_URL.'admin');
                exit;
            } else {
                $this->flasher?->addError(T::trans('Sorry something didn\'t work!'));
                header('Location:'.BASE_URL.'admin');
                exit;
            }
        }
        return $form;
    }
}