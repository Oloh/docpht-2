<?php

/**
 * This file is part of the Instant MVC micro-framework project.
 * * @package      Instant MVC micro-framework
 * @author       Valentino Pesce 
 * @link         https://github.com/kenlog
 * @copyright    2019 (c) Valentino Pesce <valentino@iltuobrand.it>
 * * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Core\Views;

use App\Model\PageModel;
use App\Model\AdminModel;
use App\Model\BackupsModel;
use App\Model\HomePageModel;
use App\Forms\VersionSelectForm;
use App\Core\Translations\T;
use Flasher\Prime\FlasherInterface;
use Flasher\Prime\Response\Presenter\PresenterInterface;

class View 
{
    protected PageModel $pageModel;
    protected AdminModel $adminModel;
    protected BackupsModel $backupsModel;
    protected HomePageModel $homePageModel;
    protected VersionSelectForm $version;
    public ?FlasherInterface $flasher;
    public ?PresenterInterface $flasherRenderer;

    public function __construct()
    {
        $this->pageModel = new PageModel();
        $this->adminModel = new AdminModel();
        $this->backupsModel = new BackupsModel();
        $this->homePageModel = new HomePageModel();
        $this->version = new VersionSelectForm();
        
        // Use the central Flasher services created in index.php
        $this->flasher = $GLOBALS['flasher'] ?? null;
        $this->flasherRenderer = $GLOBALS['flasherRenderer'] ?? null;
    }

    public function show(string $file, array $data = [])
    {
        // This makes all necessary objects available as local variables to the included template file
        $t = new T;
        $pageModel = $this->pageModel;
        $adminModel = $this->adminModel;
        $backupsModel = $this->backupsModel;
        $homePageModel = $this->homePageModel;
        $version = $this->version;
        $flasher = $this->flasher;
        $flasherRenderer = $this->flasherRenderer;

        if (!empty($data)) {
            extract($data);
        }
        
        require __DIR__ . '/../../views/' . $file;
    }

    public function load(string $title, string $path, array $viewdata = [])
    {
        $data = ['PageTitle' => T::trans($title)];
        $this->show('partial/head.php', $data);
        $this->show($path, $viewdata);
        $this->show('partial/footer.php');
    }
}