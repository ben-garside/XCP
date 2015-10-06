<?php
class Search {

	private $_db;

	public function __construct() {
		$this->_db = DB::getInstance();
	}

	public function items($term) {
		$sql = "SELECT * FROM allData WHERE ";

		$sql .= " XCP_ID like '%$term%'";
		$sql .= " OR material_id like '%$term%'";
		$sql .= " OR materialTitle like '%$term%'";
		$sql .= " OR projectNumber like '%$term%'";

		$sql .= " ORDER BY XCP_ID desc";

		$data = $this->_db->query($sql);
			if($data->count()){
				return $data->results();
			}
	}

}