<?php
class Material {

	private $_db,
			$_dbDwh,
			$_dbCls,
			$_validationRules;

	public $upi,
		   $materialData,
		   $previousVersion;

	public function __construct($upi = null) {
		if($upi) {
			$this->upi = $upi;			
		}
		$this->_db = DB::getInstance();
		$this->_dbDwh = DBDWH::getInstance();
		$this->_dbCls = DBCLS::getInstance();
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
		// need to delte existing data, then...
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

	public function getPreviousVersion() {
		foreach (explode(',', $this->materialData['supersedes']) as  $value) {
			if($value) {
				//Check if its the one. not mat group '20' and description is not a 'WD\'
				$sql = "select * from acta.material where MATERIAL_ID = '" . $value . "' and material_grp_id <> 20 and DESCRIPTION not like 'WD%'";
				$data = $this->_dbDwh->query($sql);
				if($data->count()){
					$this->previousVersion = $value;
					return $this;
				}	
			}
		}
	}

	private function hasFile($upi, $contentType = 'XML') { // XML|PDF
		if($contentType == 'XML') {
			$file = Material::toDigit($upi, 8) . ".zip";
			$queryCode = '1004';
			$sql = "SELECT left(name,8) materialId FROM [colos].[dbo].[Files] where query_id = '". $queryCode ."' AND name = '" . $file . "'";
		} else if ($contentType == 'PDF') {
			$file = Material::toDigit($upi, 8);
			$queryCode = '1002';
			$sql = "SELECT left(name,8) materialId FROM [colos].[dbo].[Files] where query_id = '". $queryCode ."' AND ContentType_id = '3003' AND left(name,8) = '" . $file . "'";
		}
		$data = $this->_dbCls->query($sql);
		if($data->count() >= 1){
			return true;
		}
		return false;
	}

	public function hasXML($upi = null) {
		if(!$upi) {
			$upi = $this->upi;
		}
		return $this->hasFile($upi, 'XML');
	}

	public function hasPDF($upi = null) {
		if(!$upi) {
			$upi = $this->upi;
		}
		return $this->hasFile($upi, 'PDF');
	}

	public static function to8digit($upi) {
		return Material::toDigit($upi, 8, '0');
	}

	public static function to18digit($upi) {
		return Material::toDigit($upi, 18, '0');
	}

	private function toDigit($val, $dig, $pad) {
		if(strlen($val) < $dig) {
			return str_pad($val, $dig, $pad, STR_PAD_LEFT);
		} elseif (strlen($val) > $dig) {
			return substr($val, strlen($val) - $dig, $dig);
		} else {
			return $val;
		}
	}

	public function getMaterialsFromProject() {
		$sql = "SELECT MATERIAL_Z02.PD_ORIG_ORG ,RIGHT(MATERIAL_CHAR.MATERIAL_ID,8) MATERIAL_ID ,PD_DESCRIPTION
				FROM acta.MATERIAL_CHAR
				LEFT JOIN ACTA.MATERIAL_Z02 ON MATERIAL_CHAR.MATERIAL_ID = MATERIAL_Z02.MATERIAL_ID
				LEFT JOIN ACTA.MATERIAL ON MATERIAL_CHAR.MATERIAL_ID = MATERIAL.MATERIAL_ID
				WHERE PD_ORIG_ORG <> 'BSI' AND PD_ORIG_ORG is not NULL AND DELETION_FLAG <> 'X' AND DESCRIPTION not like '%(HEADER)%'
				AND PROJECT = '".$this->materialData['projectNumber']."'
				ORDER BY MATERIAL_Z02.PD_ORIG_ORG asc";
		$data = $this->_dbDwh->query($sql)->results();
		return $data;
	}

}