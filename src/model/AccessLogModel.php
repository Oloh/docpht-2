<?php

declare(strict_types=1);

namespace App\Model;

use App\Core\Model\AbstractModel;

class AccessLogModel extends AbstractModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getLogs(): array
    {
        return $this->db->fetchAll('SELECT * FROM `access_log` ORDER BY `id` DESC');
    }

    public function add(string $username, string $state): void
    {
        $this->db->query('INSERT INTO `access_log`', [
            'date' => date('Y-m-d H:i:s'),
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'state' => $state
        ]);
    }

    public function getUserList(): array
    {
        return $this->db->fetchAll('SELECT DISTINCT `username` FROM `access_log` ORDER BY `username` ASC');
    }
}