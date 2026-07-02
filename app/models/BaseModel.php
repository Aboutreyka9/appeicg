<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Configs\Database;

abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Génère un code unique de type CODE-XXXXXXXX
     */
    protected function generateCode(string $prefix): string
    {
        return strtoupper($prefix) . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
}
