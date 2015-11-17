<?php
class XCP {
	private $_db,
			$_xcpData,
			$error = array(),
			$_collationFiles = null,
			$_collationContainer = null,
			$_collationDate = null,
			$_collated = false,
			$_valid = false;

	/**
	 * Main constuctor function, ruturn false if not valid and exists
	 * @param string $xcpid 
	 * @return boolean
	 */
	public function __construct($xcpid = null) {
		$this->_db = DB::getInstance();
		if($xcpid) {
			$this->xcpidChecker($xcpid);
		}
	}
	private function xcpidChecker($xcpid = null) {
		$this->validateXcpId($xcpid);
			if($this->_valid){
				if($this->checkExistance($xcpid)) {
					$this->findCollatedFiles($xcpid);
				} else {
					throw new Exception('Unable to load item: ' . $xcpid . '. This ID does not exist');
				}
			} else {
				throw new Exception('Unable to load item: ' . $xcpid . '. Invalid XCPID');
			}
		return true;
	}
	/**
	 * Validate the XCPID against a regex
	 * @param string $xcpid 
	 * @return boolean
	 */
	private function validateXcpId($xcpid = null) {
		if($xcpid){
			if (preg_match("/^XCP[0-9]{7}$/i", $xcpid)) {
    		$this->_valid = true;
    		}
		}
	}

	/**
	 * Check if the supplied XCP ID exists in the feed data table, is so set the information into $_feed
	 * @param string $xcpid 
	 * @return boolean
	 */
	private function checkExistance($xcpid) {
		if($xcpid) {
			$data = $this->_db->get('MAINDATA', array( 'XCP_ID', '=', $xcpid));
			if($data->count()) {
				$this->setXcpData($data->first());
				//$this->_xcpData = $data->first();
				return true;
			}
		}
		return false;
	}

	/**
	 * Check to see if provided Xcpid is already in use, 
	 * return true if its unique and false if it exists already
	 */
	public function checkXcpid($xcpid = null) {
		if($xcpid) {
			$db = DB::getInstance();
			$data = $db->get('FEED_DATA', array('XCP_ID','=',$xcpid));
			if($data->count()) {
				return false;
			}
			return true;
		}
		return false;
	}

	public function generateXcpid() {
		return 'XCP' . str_pad(rand(0,9999999),7,0, STR_PAD_LEFT);
	}

	public function makeXcpid() {
		$test = false;
		while ($test == false) {
			$xcpid = Xcp::generateXcpid();
			if(Xcp::checkXcpid($xcpid)){
				$test = true;
			}
		}
		return $xcpid;
	}

	/**
	 * Exclude content by asdding its details to the Exclusion table
	 */
	public function exclude($upi,$feed,$user,$comment = null) {
		$db = DB::getInstance();
		$date = date("Y/m/d H:i:s"). substr((string)microtime(), 1, 3);
		$data = array(	"UPI" => $upi,
						"FEED_ID" => $feed,
						"DT_ADDED" => $date,
						"USER_ID" => $user,
						"COMMENT" => $comment
					);
		if(!$db->insert("FEED_EXCLUTION",$data)) {
			throw new Exception("SQL ERROR");
		}
	}

	public function includeUpi($upi,$feed,$user,$comment = null) {
		$db = DB::getInstance();
		$date = date("Y/m/d H:i:s"). substr((string)microtime(), 1, 3);
		

		$xcp = $db->query("SELECT XCP_ID FROM FEED_DATA WHERE material_id = '$upi' AND feed_id = '$feed'");
		if($xcp->first()->XCP_ID) {
			throw new Exception('Item is already in feed: ' . $xcp->first()->XCP_ID);
		}
		$newXcp = Xcp::makeXcpid();
		$data = array(	"XCP_ID" => $newXcp,
						"feed_id" => $feed,
						"material_id" => $upi,
						"date_added" => $date
					);
		if(!$db->insert("FEED_DATA",$data)) {
			throw new Exception("SQL ERROR");
		} else {
			Activity::changeItemData($newXcp, 'manuallyAddedBy', $user, 'insert', 'ITEM_DATA', 1, NULL);
			Activity::changeItemData($newXcp, 'manuallyAddedOn', $date, 'insert', 'ITEM_DATA', 1, NULL);
			return $newXcp;
		}
	}

	public function getDWHData($upi) {
		if($upi){
			$sql = "SELECT MATERIAL.MATERIAL_ID materialIdLong ,RIGHT(MATERIAL.MATERIAL_ID,8) materialIdShort ,MATERIAL_Z02.PD_ORIG_ORG originatingOrg ,MATERIAL_CHAR.PROJECT projectNumber ,MATERIAL_TITLES.GENERIC_TITLE materialTitle ,MATERIAL_TITLES.PART_TITLE materialPartTitle ,MATERIAL.PD_DESCRIPTION materialDescription ,PROJECT_CHAR.PROJTYPE projectType ,MATERIAL_CHAR.PROOFPAGES pageCount ,PROJECT_DIM.PROJ_USER_STATUS as projectStatus ,PROJECT_DIM.MAX_ACHIEVED_STAGE as projectStage ,PROJECT_DIM.MAX_ACHIEVED_DATE as projectStageDate ,PROJECT_DIM.MAX_6560_DATE as projectForecastPubl ,PROJECT_MANAGER as projectManager ,PROJECT_CHAR.HREV as predRev ,PROJECT_CHAR.STDBDY AS standardsBody ,MATERIAL_CHAR.SUPERSEDES supersedes ,MATERIAL_CHAR.PD_INPUT_DATE publishedDate FROM acta.material LEFT JOIN acta.material_z02 ON material.MATERIAL_ID = MATERIAL_Z02.MATERIAL_ID JOIN acta.MATERIAL_CHAR ON MATERIAL.MATERIAL_ID = MATERIAL_CHAR.MATERIAL_ID LEFT JOIN ACTA.PROJECT_DIM ON MATERIAL_CHAR.PROJECT = PROJECT_DIM.PROJECT_NUMBER LEFT JOIN acta.PROJECT_CHAR ON PROJECT_DIM.PROJECT_ID = PROJECT_CHAR.PROJECT_ID LEFT JOIN ACTA.MATERIAL_TITLES ON MATERIAL_TITLES.MATERIAL_ID = MATERIAL.MATERIAL_ID WHERE MATERIAL.MATERIAL_ID = '0000000000".$upi."'";
			$db = DBDWH::getInstance();
			$data = $db->query($sql);
			return ($data->first());
		}
		return false;
	}

	private function setXcpData($data) {
		$outputArray = array();
		$configArray = array(	"XCP_ID" 				=> array(	"include" 			=> false, 
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "XCP ID",
																	"order"				=> "000"),
								"material_id" 			=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Material ID",
																	"order"				=> "100"),
								"feed_id" 				=> array(	"include" 			=> false,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Feed ID",
																	"order"				=> "000"),
								"feed_name" 			=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Feed Name",
																	"order"				=> "000"),
								"date_added" 			=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Date Added",
																	"order"				=> "000"),
								"projectStatus" 		=> array(	"include"			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display"			=> "Project Status",
																	"order"				=> "040"),
								"standardsBody" 		=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "SDO",
																	"order"				=> "060"),
								"originatingOrg"		=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Originating Organisation",
																	"order"				=> "050"),
								"projectType" 			=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Project Type",
																	"order"				=> "070"),
								"projectNumber" 		=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Project Number",
																	"order"				=> "080"),
								"materialDescription" 	=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Material Description",
																	"order"				=> "090"),
								"supersedes" 			=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "UPIS",
																	"display" 			=> "Supersedes",
																	"order"				=> "000"),
								"stream_id" 			=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Stream ID",
																	"order"				=> "000"),
								"pipeline_ids" 			=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Pipeline",
																	"order"				=> "000"),
								"validation_check" 		=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "boolean",
																	"display" 			=> "Is Valid?",
																	"order"				=> "000"),
								"lookFor" 				=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "",
																	"display" 			=> "Additional Material",
																	"order"				=> "000"),
								"found" 				=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "boolean",
																	"display" 			=> "Additional Material Found?",
																	"order"				=> "000"),
								"error_description"		=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> false,
																	"format" 			=> "",
																	"display" 			=> "Error",
																	"order"				=> "000"),
								"validation_error" 		=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> false,
																	"format" 			=> "",
																	"display" 			=> "Validation Error",
																	"order"				=> "000"),
								"INCLUDED" 				=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> true,
																	"format" 			=> "boolean",
																	"display" 			=> "Is Included",
																	"order"				=> "000"),
								"EXCLUTION_ID" 			=> array(	"include" 			=> true,
																	"includeIfEmpty" 	=> false,
																	"format" 			=> "",
																	"display" 			=> "Exclution ID",
																	"order"				=> "000")
							);
		foreach ($data as $key => $value) {
			if($configArray[$key]["include"]){
				if($value || $configArray[$key]["includeIfEmpty"]){
					$outputArray[$key] = $configArray[$key];
					switch ($configArray[$key]["format"]) {
					 	case 'boolean':
					 		$outputArray[$key]["value"] = ($value == 1) ? "YES" : "NO";
					 		break;
					 	case 'UPIS':
					 		$outputArray[$key]["value"] = preg_replace("/(,)(?=[0-9])/", "<br>", preg_replace("/,$/", "", preg_replace("/0000000000/", "", $value)));
					 		break;
					 	default:
					 		$outputArray[$key]["value"] = $value;
					 		break;
					}
				}
			}
		$order = array();
		foreach ($outputArray as $key => $row)
		{
		    $order[$key] = $row['order'];
		}
		array_multisort($order, SORT_DESC, $outputArray);
		$this->_xcpData = $outputArray;
		}
	}

	public function findCollatedFiles($xcpid) {
		if($xcpid) {
			$data = $this->_db->get('FILE_COLLATION', array( 'XCP_ID', '=', $xcpid));
			if($data->count()) {
				$a = array();
				$a['collationContainer'] = $data->first()->FILE_LOCATION;
				$a['collationDate'] = $data->first()->COLLATION_DATE;
				$a['files'] = $data->results();

				$this->_collationFiles = $data->results();
				$this->_collationContainer = $a;
				$this->_collated = true;
				return true;
			}
		}
		return false;		
	}

	/**
	 * Returns if the XCPID is valid or not
	 * @return boolean
	 */
	public function isValid() {
		return $this->_valid;
	}

    /**
     * Return the feed information for the current XCPID
     * @return class object
     */
	public function getFeedData() {
		return $this->_xcpData;
	}


	public function getCollationData() {
		return $this->_collationContainer;
	}

	public function isCollated() {
		return $this->_collated;
	}
	/**
	 * Returns errors, if any
	 * @return array 
	 */
	public function whatError() {
		return $this->_error;
	}
}