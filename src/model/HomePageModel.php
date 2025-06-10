<?php

declare(strict_types=1);

namespace App\Model;

use App\Core\Model\AbstractModel;

class HomePageModel extends AbstractModel
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getHomePage(): ?array
    {
        $result = $this->db->fetch('SELECT `content` FROM `home_page` LIMIT 1');
        return $result ? (array) $result : null;
    }

    public function updateHomePage(string $content): void
    {
        $this->db->query('UPDATE `home_page` SET `content` = ?', $content);
    }
}