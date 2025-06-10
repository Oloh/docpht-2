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

namespace App\Controller;

use App\Core\Controller\BaseController;
use App\Core\Translations\T;

class HomeController extends BaseController
{
    public function index()
    {
        $this->view->load('Home', 'home.php', ['page' => $this->homePageModel->getHomePage()]);
    }

    public function page(array $vars)
    {
        $page = $this->pageModel->getPage($vars['name']);

        if ($page) {
            $this->view->load($page['title'], 'page/page.php', ['page' => $page]);
        } else {
            $this->getPage();
        }
    }

    public function search(array $vars)
    {
        $form = $this->searchForm->create();
        $results = [];

        if ($form->isSuccess()) {
            $values = $form->getValues();
            $results = $this->searchModel->find($values->search);
        }

        $this->view->load('Search', 'search_results.php', [
            'form' => $form,
            'results' => $results,
        ]);
    }
    
    public function getPage()
    {
        $this->view->load('404 Not Found', 'error_page.php', ['errorCode' => '404', 'errorMessage' => T::trans('Page not found.')]);
    }
}