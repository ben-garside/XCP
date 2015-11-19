<?php
class XCP {
	private $_db,
			$_xcpData,
			$_error = array(),
			$_collationFiles = null,
			$_collationContainer = null,
			$_collationDate = null,
			$_collated = false,
			$_valid = false,
			$_xcpDataRaw;

	public $xcpid;

	/**
	 * Main constuctor function, ruturn false if not valid and exists
	 * @param string $xcpid 
	 * @return boolean
	 */
	public function __construct($xcpid = null) {
		$this->_db = DB::getInstance();
		if($xcpid) {
			$this->xcpidChecker($xcpid);
			$this->xcpid = $xcpid;
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
				$this->_xcpDataRaw = $data->first();
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

	public function includeUpi($upi,$feed,$user,$comment = null, $stage) {
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
			if($comment){
				Activity::changeItemData($newXcp, 'manuallyAddedBecause', $comment, 'insert', 'ITEM_DATA', 1, NULL);
			}
			$activity = new Activity($newXcp);
			$activity->moveToStage($stage);
			$material = new Material($upi);
			$material->setDWHData();
			$material->validate();
			return $newXcp;
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
								"feed_id" 				=> array(	"include" 			=> true,
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

	public function findPipeline() {
		$feed_id = $this->_xcpDataRaw->feed_id;
		switch ($feed_id) {
			case '4':
				$pipeline = 8;
				break;
			case '3':
				$pipeline = 6;
				break;
			case '2':
				$pipeline = 1;
				break;
			case '1':
				// will be 2, 4, 5, 6 or 7 ...
				$upi = $this->_xcpDataRaw->material_id;
				$material = new Material($upi);
				$origOrg = $this->_xcpDataRaw->originatingOrg;
				switch ($origOrg) {
					case 'AAMI':
						$pipeline = 5;
						break;
					case 'ASTM':
						$pipeline = 6;
						break;
					case 'BSI':
						// cue headfuck...
						$projType = $this->_xcpDataRaw->projectType;
						switch ($projType) {
							case 'AM':
							case 'CR':
								// Get list of superseded materials, and get the one we want (the previous published version)
								if(!$fileToCheck = $material->getPreviousVersion()->previousVersion){
									$error = 'No SDO given';
									$pipeline = 99;
									break;
								}
								// as per #18 all CEN items will be a PL2 as CEN XML content is not production ready
								if($this->_xcpDataRaw->standardsBody == 'CEN') {
									$pipeline = 2;
									break;
								}
								// need to check for files
								if($material->hasXML($fileToCheck)) {
									$this->updateFound($fileToCheck, 1);
									$pipeline = 7;
									break;
								} else {
									$this->updateFound($fileToCheck, 0);
									$pipeline = 2;
									break;
								}
								break;
							case 'NW':
							case 'RV':
							case 'ND':
							case 'IM':
								// ....
								$matInProject = $material->getMaterialsFromProject();
								switch (count($matInProject)) {
									case '0':
										if($this->_xcpDataRaw->standardsBody == 'BSI') {
											$fileToCheck = $upi;
											if($material->hasXML($fileToCheck)) {
												$this->updateFound($fileToCheck, 1);
												$pipeline = 4;
												break;
											} else {
												$this->updateFound($fileToCheck, 0);
												$pipeline = 2;
												break;
											}
										} else {
											$error = 'No materials returned... check project?';
											$pipeline = 91;
											break;
										}
										break;
									case '1':
										$fileToCheck = $matInProject[0]->MATERIAL_ID;
										if($material->hasXML($fileToCheck)) {
											$this->updateFound($fileToCheck, 1);
											$pipeline = 4;
											break;
										} else {
											$this->updateFound($fileToCheck, 0);
											$pipeline = 2;
											break;
										}
										break;
									case '2':
										$o = $matInProject[0]->PD_ORIG_ORG . $matInProject[1]->PD_ORIG_ORG;
										if($o == 'CENISO' || $o == 'CENELECISO' || $o = 'CENIEC' || $o == 'CENELECIEC') {
											$fileToCheck = $matInProject[0]->MATERIAL_ID;
											if($material->hasXML($fileToCheck)) {
												$this->updateFound($fileToCheck, 1);
												$pipeline = 4;
												break;
											} else {
												$this->updateFound($fileToCheck, 0);
												$pipeline = 2;
												break;
											}
										} else {
											$error = 'Incorrect materials returned... check project?';
											$pipeline = 92;
											break;
										}
										break;
									default:
										$error = 'No BSI materials in project';
										$pipeline = 94;
										break;
								}
								break;
							default:
								$error = 'No project type given';
								$pipeline = 96;
								break;
						}
						break;
					default:
						$error = 'No SDO given';
						$pipeline = 99;
						break;
				}
				#$pipeline = 00;
				break;
			default:
				$pipeline = 90;
				break;
		}
		return $pipeline;
	}

	private function updateFound($material, $found) {
		$checkSql = "SELECT id FROM [dbo].[foundTest] WHERE XCPID = '" . $this->xcpid . "'";
		$data = $this->_db->query($checkSql);
		$array = array('XCPID' => $this->xcpid, 'lookFor' => Material::to8digit($material), 'found' => $found);
		if($data->count() >= 1) {
			//Update
			$id = $data->first()->id;
			$this->_db->update('dbo.foundTest', $id, 'id', $array);
		} else {
			$this->_db->insert('dbo.foundTest', $array);
		}
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