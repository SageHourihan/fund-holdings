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
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($result && isset($result['id']))
                return $result;
            else
                return false;    
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        if($error) error_log(json_encode($error));
    }
    
    public function insert_holding($fund_id, $holding){
        $error = null;
        try {
            // clean shares of commma
            $shares = str_replace(',', '', $holding['shares']);
            $stmt = $this->pdo->prepare("INSERT INTO fund_holdings (fund_id, stock_ticker, stock_name, percent_weight, shares) VALUES(:fund_id, :stock_ticker, :stock_name, :percent_weight, :shares)");
        $stmt->execute([
        ":fund_id" => $fund_id,
        ":stock_ticker" => $holding['ticker'],
        ":stock_name" => $holding['company'],
        ":percent_weight" => $holding['percentage'],
        ":shares" => $shares
        ]);
    
        return true;

        } catch (Exception $e) {
            $error = $e->getMessage();;
        }   
        if($error) error_log(json_encode($error));
    }

}
