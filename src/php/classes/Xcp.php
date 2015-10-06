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

	public function exclude($upi,$feed,$user,$comment = null) {
		$db = DB::getInstance();
		$data = array(	"UPI" => $upi,
						"FEED_ID" => $feed,
						"DT_ADDED" => date(),
						"USER_ID" => $user,
						"COMMENT" => $comment
					);
		if(!$db->insert("FEED_EXCLUTION",$data)) {
			throw new Exception("SQL ERROR");
		}
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