<?php

namespace DocPHT\Controller;

use DocPHT\Core\Controller\BaseController;
use DocPHT\Core\Http\Session;
use DocPHT\Model\SearchModel;
use System\Request;

class HomeController extends BaseController
{
    private object $searchModel;

    public function __construct(Session $session, Request $request)
    {
        parent::__construct($session, $request);
        $this->searchModel = new SearchModel();
    }

	public function index()
	{
	    $home_page = $this->pageModel->getHomePage();
	    
		$this->view->show('partial/head.php', ['PageTitle' => '']);
        
		if ($home_page && file_exists($home_page['path'])) {
		    $values = (include $home_page['path']);
		    $this->view->show('page/page.php', [
                'values' => $values ?? [],
                'page_id' => $home_page['id'],
                'is_published' => $home_page['is_published']
            ]);
		} else {
		    $this->view->show('home.php');
		}
		$this->view->show('partial/footer.php');
	}

    public function page(string $slug)
    {
        $page = $this->pageModel->getPageBySlug($slug);
        
        if (!$page || (!$page['is_published'] && !$this->session->get('Active'))) {
            header('Location: ' . BASE_URL . '404');
            exit;
        }
        
        $this->view->show('partial/head.php', ['PageTitle' => $page['title']]);
        if (file_exists($page['path'])) {
            $values = (include $page['path']);
            $this->view->show('page/page.php', [
                'values' => $values ?? [],
                'page_id' => $page['id'],
                'is_published' => $page['is_published']
            ]);
        } else {
            header('Location: ' . BASE_URL . '404');
            exit;
        }
        $this->view->show('partial/footer.php');
    }

    public function search()
    {
        $query = $this->request->body->search ?? '';
        if (empty($query)) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
        $isLoggedIn = $this->session->get('Active');
        $results = $this->searchModel->find($query, $isLoggedIn);

        $this->view->load(
            'Search Results',
            'search_results.php',
            [
                'query' => $query,
                'results' => $results
            ]
        );
    }
}