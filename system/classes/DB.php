<?php

namespace Lampa;

use \PDO;

class DB
{
	public $pdo;
	
	public static function factory() {
		$db = new DB();
		try {
			$config = Config::factory(array('databse'));
			$opt  = array(
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => TRUE,
			);
			$dsn = 'mysql:host='.$config->databse['hostname'].';dbname='.$config->databse['database'].';charset='.$config->databse['charset'];
			$db->pdo = new PDO($dsn, $config->databse['username'], $config->databse['password'], $opt);
		}
		catch(\PDOException $e)
		{
			die($e->getMessage());
		}
		return $db;
	}
	
    public function run($sql, $args = [])
    {
        $stmt = $this->pdo->prepare($sql);
        
        if(!$stmt){
            echo "\nPDO::errorInfo():\n"; 
            print_r($stmt->errorInfo());
            die();
        }
		try {
			$stmt->execute($args);
		} catch (\PDOException $e) {
			echo "Database error: " . $e->getMessage();
			die();
		}
        return $stmt;
    }
	
    public function insertAndGetLastId($sql, $args = [])
    {
        $stmt = $this->pdo->prepare($sql);
        
        if(!$stmt){
            echo "\nPDO::errorInfo():\n"; 
            print_r($dbh->errorInfo());
            die();
        }
		try {
			$stmt->execute($args);
		} catch (\PDOException $e) {
			echo "Database error: " . $e->getMessage();
			die();
		}
        return $this->pdo->lastinsertid();
    }
}