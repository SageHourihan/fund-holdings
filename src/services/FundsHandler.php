<?php 

namespace Services;

require_once __DIR__ . '/../../config/db.php';
use Database;
use \PDO;

class FundsHandler {
    private $pdo;

    public function __construct(){
        $database = new Database();
        $this->pdo = $database->getPdo();
    }
}
