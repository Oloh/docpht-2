<?php

declare(strict_types=1);

namespace DocPHT\Core\Controller;

use DocPHT\Core\Http\Session;
use DocPHT\Core\Views\View;
use DocPHT\Model\PageModel;
use Nette\Mail\SendmailMailer;
use Plasticbrain\FlashMessages\FlashMessages;
use System\Request;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;

abstract class BaseController
{
    protected Session $session;
    protected Request $request;
    protected View $view;
    protected Translator $translator;
    protected PageModel $pageModel;
    public FlashMessages $msg;
    protected SendmailMailer $mailer;

    public function __construct(Session $session, Request $request)
    {
        $this->session = $session;
        $this->request = $request;
        $this->mailer = new SendmailMailer();

        // Initialize Translator
        $this->translator = new Translator(LANGUAGE);
        $this->translator->addLoader('php', new PhpFileLoader());
        $langFile = 'src/translations/' . LANGUAGE . '.php';
        if (file_exists($langFile)) {
            $this->translator->addResource('php', $langFile, LANGUAGE);
        }

        // Initialize PageModel
        $this->pageModel = new PageModel();

        // Prepare sidebar data
        $isLoggedIn = $this->session->get('Active');
        $topics = $isLoggedIn ? $this->pageModel->getUniqTopics() : $this->pageModel->getUniqPublishedTopics();
        
        $sidebarData = [];
        if ($topics) {
            foreach ($topics as $topic) {
                $pages = $isLoggedIn ? $this->pageModel->getPagesByTopic($topic) : $this->pageModel->getPublishedPagesByTopic($topic);
                if ($pages) {
                    $sidebarData[$topic] = $pages;
                }
            }
        }

        // Initialize Flash Messages
        $this->msg = new FlashMessages();

        // Initialize View and provide default variables
        $this->view = new View();
        $this->view->addDefault('t', $this->translator);
        $this->view->addDefault('request', $this->request);
        $this->view->addDefault('sidebarData', $sidebarData);
        $this->view->addDefault('msg', $this->msg);
    }
}