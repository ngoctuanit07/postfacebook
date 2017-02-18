<?php  if (!defined('ABSPATH')) exit('No direct script access allowed');
/*
|---------------------------------------------------------
| DB - A helper class to process database operations.
| @name DB
| @author Gounane abdallah - http://www.fb.com/gounane/
|---------------------------------------------------------
|
*/
class DB{
	private static $_instance = null;
	private $_dbFile,
			$_pdo,
			$_query,
			$_error = false,
			$_results,
			$_count = 0,
			$_lastInsertedId = 0;

	private function __construct(){
		$this->_dbFile = ABSPATH."/database/".Config::get('db/dbname').".db";	
		if ( ! file_exists($this->_dbFile) ) {
			die("Database File not Found!");
		}else{
			try{
				$this->_pdo = new PDO("sqlite:".$this->_dbFile);
			}catch(PDOException $e){
				die($e->getMessage());
			}
		}
	}

	public static function CreateDbFile(){
		try{
			new PDO("sqlite:".ABSPATH."/database/".Config::get('db/dbname').".db");
		}catch(PDOException $e){
			die($e->getMessage());
		}
	}
	
	public static function getInstance(){
		if(!isset(self::$_instance)){
			self::$_instance = new DB();
		}
		return self::$_instance;
	}

	public static function close(){
		$thisClass = new DB();
		$thisClass->_pdo = null;
	}
	
	public function query($sql, $params = array()){
		$this->_error = false;
		if(DEBUG_MODE){
			$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

		if($this->_query = $this->_pdo->prepare($sql)){
			$x = 1;
			if(count($params)){
				foreach($params as $param){
					$this->_query->bindValue($x, $param,PDO::PARAM_STR);
					$x++;
				}
			}
			
			try{
				if($this->_query->execute()){
					$this->_count = $this->_query->rowCount();
					$this->_lastInsertedId = $this->_pdo->lastInsertId();
				}else{
					$this->_error = true;
				}
			}catch(PDOException $ex){
				$this->_error = true;
				throw new Exception($ex->GetMessage());
			}
			
		}
		return $this;
	}
	
	public function queryGet($sql, $params = array()){
		$this->_error = false;
		if(DEBUG_MODE){
			$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		try{
			if($this->_query = $this->_pdo->prepare($sql)){
				$x = 1;
				if(count($params)){
					foreach($params as $param){
						$this->_query->bindValue($x, $param,PDO::PARAM_STR);
						$x++;
					}
				}

				if($this->_query->execute()){
					$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
					$this->_count = count($this->_results);
				}else{
					$this->_error = true;
				}
			}
		}catch(Exception $ex){
			throw new Exception("Database Error : ".$ex->GetMessage());
		}
		
	
		return $this;
	}
	
	public function action($action, $table, $where = array()){
		if(count($where) === 3){
			$operators = array('=', '>', '<', '>=', '<=');

			$field = $where[0];
			$operator = $where[1];
			$value = $where[2];

			if(in_array($operator, $operators)){
				$sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";

				if(strpos(strtoupper(trim($sql)),'SELECT') == 0){
					$ExeAction = $this->queryGet($sql, array($value))->error();
				}else{
					$ExeAction = $this->query($sql, array($value))->error();
				}
				
				if(!$ExeAction){
					return $this;
				}
			}
		}
		return false;
	}

	public function get($table, $where){
		return $this->action('SELECT *', $table, $where);
	}

	public function delete($table, $where){
		return $this->action('DELETE', $table, $where);
	}

	public function insert($table, $fields = array()){
		$keys = array_keys($fields);
		$values = '';
		$x = 1;

		foreach($fields as $field){
			$values .= '?';
			if($x < count($fields)){
				$values .= ', ';
			}
			$x++;
		}

		$sql = "INSERT INTO {$table} (`" . implode('`, `', $keys) . "`) VALUES ({$values})" ;
		
		if(!$this->query($sql, $fields)->error()){
			return true;
		}
		return false;
	}

	public function update($table, $where, $id, $fields){
		$set = '';
		$x = 1;

		foreach($fields as $name => $value){
			$set .= "{$name} = ?";
			if($x < count($fields)){
				$set .= ', ';
			}
			$x++;
		}

		$sql = "UPDATE {$table} SET {$set} WHERE {$where} = '{$id}'";

		if(!$this->query($sql, $fields)->error()){
			return true;
		}

		return false;
	}

	public function results(){
		return $this->_results;
	}

	public function first(){
		if($this->count())
			return $this->_results[0];
		return null;
	}

	public function error(){
		return $this->_error;
	}

	public function count(){
		return $this->_count;
	}
	
	public function lastInsertedId(){
		return $this->_lastInsertedId;
	}
}
?>