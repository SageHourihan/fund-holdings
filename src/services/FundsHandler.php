<?php 

namespace services;

require_once __DIR__ . '/../../config/db.php';
use Database;
use Exception;
use \PDO;

class FundsHandler {
    private $pdo;

    public function __construct(){
        $database = new Database();
        $this->pdo = $database->getPdo();
    }

    public function insert_fund($fund){
        $error = null;
        try{
             $stmt = $this->pdo->prepare("INSERT INTO funds (ticker) VALUES (:ticker)"); 
            $stmt->execute([
            ':ticker' => $fund,
          //  ':name' => $name
        ]);
        }catch(Exception $e){
            $error = $e->getMessage();
        }
        if($error) error_log(json_encode($error));
     }

    public function get_fund($fund){
        $error = null;
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM funds WHERE ticker = :fund");
            $stmt->execute([
            ":fund" => $fund
            ]);
            $stmt->fetch();

        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        if($error) error_log(json_encode($error));
    }
}
