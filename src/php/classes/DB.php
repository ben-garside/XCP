<?php
class DB {
	private static $_instance = null;
	private $_pdo,
			$_query,
			$_error = false,
			$_errorInfo = null,
			$_results,
			$_count = 0,
			$_connAttempts = 5,
			$_columns = array();

	private function __construct(){
		
		$retries = $this->_connAttempts;
		while ($retries > 0)
		{
			try
			{
				$this->_pdo = new PDO('sqlsrv:Server=' . Config::get('mysql/host') . ';Database=' . Config::get('mysql/db'), Config::get('mysql/username'), Config::get('mysql/password'));
				$retries = 0;
			}
			catch (PDOException $e)
			{
				if($e->getCode() == "08001") { // Only retry if the error is 08001 - Connection error.
					echo "Something went wrong, retrying...\n";
					$retries--;
					usleep(500); // Wait 0.5s between retries.
				} else {
					die($e->getMessage());
				}
				
			}
		}

	}

	public static function getInstance() {
		if(!isset(self::$_instance)) {
			self::$_instance = new DB();
		}
		return self::$_instance;
	}

	public function query($sql, $params = array()) {
		$this->_error = false;
		if($this->_query = $this->_pdo->prepare($sql)) {
			$x = 1;
			if(count($params)) {
				foreach ($params as $param) {
					$this->_query->bindvalue($x, $param);
					$x++;
				}
			}
			if($this->_query->execute()) {
				$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
				unset($this->_columns);
				foreach ($this->_results[0] as $key => $value) {

					$this->_columns[] = $key;
				}

				$this->_count = $this->_query->rowCount();
			} else {
				echo 'asdvasfv';
				$this->_error = true;
				$this->_errorInfo = $this->_query->errorInfo();
			}
		}

		return $this;
	}

	public function action($action, $table, $where = array()){
		if(count($where) === 3) {
			$operators = array('=', '>', '<', '<=', '>=');

			$field 		= $where[0];
			$operator 	= $where[1];
			$value 		= $where[2];

			if(in_array($operator, $operators)) {
				$sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";
				if(!$this->query($sql, array($value))->error()) {
					return $this;
				}
			}
		}
		return false;
	}

	public function get($table, $where) {
		return $this->action('SELECT *', $table, $where);

	}

	public function getAll($table) {
		$sql = "SELECT * FROM {$table}";
				if(!$this->query($sql)->error()) {
					return $this;
				}

	}

	public function delete($table, $where) {
		return $this->action('DELETE', $table, $where);
	}

	public function insert($table, $fields = array()) {
		 if(count($fields)) {
		 	$keys = array_keys($fields);
		 	$values = null;
		 	$x = 1;

		 	foreach ($fields as $field) {
		 		$values .= '?';
		 		if($x < count($fields)) {
		 			$values .= ', ';
		 		}
		 		$x++;
		 	}

		 	$sql = "INSERT INTO {$table} (" . implode(', ', $keys) . ") VALUES ({$values})";
		 	if(!$this->query($sql, $fields)->error()) {
		 		return true;
		 	}
		 }
		 return false;
	}

	public function update($table, $id, $idName, $fields) {
		$set = '';
		$x = 1;

		foreach($fields as $name => $value) {
			$set .= "{$name} = ?";
			if($x < count($fields)) {
				$set .= ", ";
			}
			$x++;
		}

		$sql = "UPDATE {$table} SET {$set} WHERE {$idName} = {$id}";
		if(!$this->query($sql, $fields)->error()) {
			return true;
		}
		return false;
	}

	public function results() {
		return $this->_results;
	}

	public function first() {
		return $this->results()[0];
	}

	public function error() {
		return $this->_error;
	}

	public function errorInfo() {
		return $this->_errorInfo;
	}

	public function count() {
		return $this->_count;
	}

	public function columns() {
		return $this->_columns;
	}

}
