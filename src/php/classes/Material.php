<?php
class Material {
	private $_db,
			$_dbDwh,
			$_validationRules;

	public $upi,
		   $materialData;

	public function __construct($upi = null) {
		if($upi) {
			$this->upi = $upi;			
		}
		$this->_db = DB::getInstance();
		$this->_dbDwh = DBDWH::getInstance();
		if($this->checkDWHdataIsAddable($upi)){
			$this->getDWHData();
		} else {
			$this->getLocalData();
		}
		
	}

	private function getLocalData() {
		$upi = $this->upi;
		$sql = "SELECT dataType, dataValue FROM DWH_DATA WHERE material_id = '".$upi."'";
		$data = $this->_db->query($sql)->results();
		$out = array();
		foreach ($data as $key => $value) {
			$out[$value->dataType] = $value->dataValue;
		}
		$this->materialData = $out;
	}

	public function refreshDWHData() {
		$this->getDWHData();
	}

	private function getDWHData() {
		$upi = $this->upi;
		$sql = "SELECT MATERIAL.MATERIAL_ID materialIdLong ,RIGHT(MATERIAL.MATERIAL_ID,8) materialIdShort ,MATERIAL_Z02.PD_ORIG_ORG originatingOrg ,MATERIAL_CHAR.PROJECT projectNumber ,MATERIAL_TITLES.GENERIC_TITLE materialTitle ,MATERIAL_TITLES.PART_TITLE materialPartTitle ,MATERIAL.PD_DESCRIPTION materialDescription ,PROJECT_CHAR.PROJTYPE projectType ,MATERIAL_CHAR.PROOFPAGES pageCount ,PROJECT_DIM.PROJ_USER_STATUS as projectStatus ,PROJECT_DIM.MAX_ACHIEVED_STAGE as projectStage ,PROJECT_DIM.MAX_ACHIEVED_DATE as projectStageDate ,PROJECT_DIM.MAX_6560_DATE as projectForecastPubl ,PROJECT_MANAGER as projectManager ,PROJECT_CHAR.HREV as predRev ,PROJECT_CHAR.STDBDY AS standardsBody ,MATERIAL_CHAR.SUPERSEDES supersedes ,MATERIAL_CHAR.PD_INPUT_DATE publishedDate FROM acta.material LEFT JOIN acta.material_z02 ON material.MATERIAL_ID = MATERIAL_Z02.MATERIAL_ID JOIN acta.MATERIAL_CHAR ON MATERIAL.MATERIAL_ID = MATERIAL_CHAR.MATERIAL_ID LEFT JOIN ACTA.PROJECT_DIM ON MATERIAL_CHAR.PROJECT = PROJECT_DIM.PROJECT_NUMBER LEFT JOIN acta.PROJECT_CHAR ON PROJECT_DIM.PROJECT_ID = PROJECT_CHAR.PROJECT_ID LEFT JOIN ACTA.MATERIAL_TITLES ON MATERIAL_TITLES.MATERIAL_ID = MATERIAL.MATERIAL_ID WHERE MATERIAL.MATERIAL_ID = '0000000000".$upi."'";
		$this->materialData = $this->_dbDwh->query($sql)->first();
	}

	private function checkDWHdataIsAddable($upi) {
		$sql = "SELECT material_id FROM DWH_DATA WHERE material_id = '".$upi."'";
		$data = $this->_db->query($sql);
		if($data->count()){
			return false;
		}
		return true;
	}

	public function setDWHData() {
		$upi = $this->upi;
			if($this->checkDWHdataIsAddable($upi)) {
				foreach ($this->materialData as $key => $value) {
					$test = array('material_id' => $upi, 'dataType' => $key, 'dataValue' => $value);
					$this->_db->insert('DWH_DATA', $test);
				}
			} else {
				return false;
			}
	}

	public function getValidationRules() {
		$sql = "SELECT [field], [required], [validation] FROM [DWH_VALIDATION_RULES]";
		$data = $this->_db->query($sql);
		$out = array();
		foreach ($data->results() as $value) {
			$out[$value->field] = array('required' => $value->required, 'validation' => $value->validation);
		}
		$this->_validationRules = $out;
	}

	public function validate() {
		if(!$this->_validationRules) {
			$this->getValidationRules();
		}
		$valid = true;
		foreach ($this->materialData as $key => $value) {
			if($rule = $this->_validationRules[$key]){
				// check againsdt rules
				$req = $rule['required'];
				$val = $rule['validation'];
				if($req == 1 && !$value){
					$valid = false;
					$validNote = $validNote . '[' . $key . "] is a required field|";
				}
				if($val){
					if(preg_match("|" . $val . "|", $value)) {
					} else {
						$valid = false;
						$validNote = $validNote . '[' . $key . "] is not vlaid (".$val.")|";
					}
				}
			}
		}
		if(!$valid){
			$this->setVaildation(rtrim($validNote, "|"));
		} else {
			$this->setVaildation();
		}
	}

	private function setVaildation($notes = null) {
		// check if its update or insert
		$val = ($notes ? 0 : 1 );
		$dt = date("Y/m/d H:i:s"). substr((string)microtime(), 1, 3);
		if($this->_db->query("SELECT * FROM [DWH_validation] WHERE upi = '" . $this->upi . "'")->count()) {
			//update
			$this->_db->update('DWH_validation', $this->upi, 'upi', array('validation_check' => $val, 'validation_dt' => $dt, 'validation_error' => $notes));
		} else {
			//insert
			$this->_db->insert('DWH_validation', array('upi' => $this->upi, 'validation_check' => $val, 'validation_dt' => $dt, 'validation_error' => $notes));
			if($this->_db->error()) {
				print_r($this->_db->errorInfo());
			}
		}
	}

}