<?php
class Activity {
	
	public $xcpid = null;

	private $_db,
			$_activies,
			$_actRules,
			$_xcpInfo,
			$_currentDetails;

	public function __construct($xcpid = null) {
		$this->_db = DB::getInstance();
		$this->findActivities();
		if($xcpid){
			$this->xcpid = $xcpid;
			$this->findInfo($xcpid);
			$this->_getCurrentDetails();
			$this->getActivitiesForItem($xcpid);	
		}
	}

	private function findInfo($xcpid = null) {
		if($xcpid) {
			$data = $this->_db->get('mainData', array('xcp_id', '=', $xcpid));
			if($data->count()) {
				$this->_xcpInfo = $data->first();
			}
		}
	}

	public function getInfo() {
		return $this->_xcpInfo;
	}

	private function getActivitiesForItem ($xcpid = null) {
		$data = $this->_db->get('STREAM_ALLOCATION', array('XCP_ID', '=', $xcpid));
		if($data->count()) {
			$stream = $data->first()->STREAM_ID;
			$data = $this->_db->query("SELECT act_out, status_out FROM ACT_MAPPING_VIEW WHERE act_in = '" . $this->_currentDetails->ACT . "' AND status_in = '" . $this->_currentDetails->STATUS . "' AND pipeline_id = " . $this->_xcpInfo->stream_id);
			if($data->count()) {
				$return = $data->results();
				$rulesArray = array();
				foreach ($return as $value) {
					if(is_numeric($value->status_out)){
						$rulesArray[$value->act_out . ":" . $value->status_out] = $this->getActivityDescription($value->act_out) . ":" . $this->getStatusDescription($value->status_out, $value->act_out);
					} elseif(substr($value->status_out,0,1) == "*") {
						// All available at ACT
						$allData = $this->_db->query("SELECT status FROM [ACT_STATUS_2] WHERE act = '" . $value->act_out . "'");
						$allDatareturn = $allData->results();
						foreach ($allDatareturn as $allDataValue) {
							$rulesArray[$value->act_out . ":" . $allDataValue->status] = $this->getActivityDescription($value->act_out) . ":" . $this->getStatusDescription($allDataValue->status, $value->act_out);
						}
					}
				}
			}
			$this->_actRules = $rulesArray;
		}
		return false;
	}

	public function findActivities() {
		$data = $this->_db->getAll('ACT_DETAIL');
		
		if($data->count()) {
			$this->_activies = $data->results();
		}
	}

	public function getActivityDescription($activity) {

		$data = $this->_db->get('ACT_DETAIL', array('ID', '=', $activity));

		if($data->count()) {
			return $data->first()->SHORT_NAME;
		}
	}

	public static function splitStage($stage, $delim = null) {
		if($stage) {
			if(!$delim) {
				$delim = ":";
			}
			$delPos = strpos($stage, $delim);
			$act = substr($stage ,0 ,$delPos);
			$satus = substr($stage, $delPos+1, strlen($stage)-1);
			return array("stage" => $stage, "activity" => $act, "status" => $satus);
		}
		return false;
	}

	public static function getStatusDescription($status, $activity) {
		$db = DB::getInstance();
		$sql = "SELECT name FROM [ACT_STATUS_2] WHERE act = '$activity' AND status = '$status'";
		$data = $db->query($sql);
		if($data->count()) {
			return $data->first()->name;
		} else {
			return 'error';
		}
	}

	public static function getStatusDescriptionDescription($status, $activity) {
		$db = DB::getInstance();
		$sql = "SELECT [description] FROM [ACT_STATUS_2] WHERE act = '$activity' AND status = '$status'";
		$data = $db->query($sql);
		if($data->count()) {
			return $data->first()->description;
		} else {
			return 'error';
		}
	}

	public function getActionDescription($action) {

		$data = $this->_db->get('ACT_STATUS_2', array('ID', '=', $action));

		if($data->count()) {
			return $data->first()->STATUS;
		}
	}

	public function getAllActivities() {
		return $this->_activies;
	}


	public function getActRules() {
		return $this->_actRules;
	}

	private function _getCurrentDetails() {
		if($this->xcpid) {
			$data = $this->_db->query("SELECT TOP 1 * FROM ACT_AUDIT WHERE XCPID = '" . $this->xcpid . "' ORDER BY ID DESC");
			if($data->count()){
				$this->_currentDetails = $data->first();
			}
		}
	}

	public function getCurrentActivity() {
		return $this->_currentDetails->ACT;
	}

	public function getCurrentStatus() {
		return $this->_currentDetails->STATUS;
	}

	public function getCurrentAll() {
		return $this->_currentDetails;
	}

	public function unAssign() {
		$this->_getCurrentDetails();
		$user = new User();
		$sql = 		"UPDATE [dbo].[ACT_AUDIT]
					SET allocatedTo = NULL, allocatedBy = NULL, allocatedOn = NULL
					WHERE ID = (SELECT TOP 1 AUDIT.ID
					FROM mainData
					OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
					WHERE AUDIT.ACT = " . $this->getCurrentActivity() . " AND AUDIT.XCPID IS NOT NULL AND XCP_ID = '$this->xcpid')";
		$data = $this->_db->query($sql);
		if($data->count()){
			return 'OK';
		} else {
			return 'ERROR';
		}
	}

	public function claim() {
		$this->_getCurrentDetails();
		$user = new User();
		$sql = 		"UPDATE [dbo].[ACT_AUDIT]
					SET allocatedTo = " . $user->data()->id . ", allocatedBy = " . $user->data()->id . ", allocatedOn = getdate()
					WHERE ID = (SELECT TOP 1 AUDIT.ID
					FROM mainData
					OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
					WHERE AUDIT.ACT = " . $this->getCurrentActivity() . " AND AUDIT.XCPID IS NOT NULL AND XCP_ID = '$this->xcpid')";
		$data = $this->_db->query($sql);
		if($data->count()){
			return 'OK';
		} else {
			return 'ERROR';
		}
	}

	public function assign($assignerId) {
		$this->_getCurrentDetails();
		$user = new User();
		$sql = 		"UPDATE [dbo].[ACT_AUDIT]
					SET allocatedTo = " . $user->data()->id . ", allocatedBy = " . $assignerId . ", allocatedOn = getdate()
					WHERE ID = (SELECT TOP 1 AUDIT.ID
					FROM mainData
					OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
					WHERE AUDIT.ACT = " . $this->getCurrentActivity() . " AND AUDIT.XCPID IS NOT NULL AND XCP_ID = '$this->xcpid')";
		$data = $this->_db->query($sql);
		if($data->count()){
			return 'OK';
		} else {
			return 'ERROR';
		}
	}


	public function moveToStage($stage) {
		if($this->xcpid) {
			$user = new User();
			$userId = $user->data()->id;
			try {
				$this->moveToActivity(Activity::splitStage($stage)[activity], Activity::splitStage($stage)[status], $userId, true, 'rules');
			} catch(Exception $e) {
				return $e->getMessage();
			}
			return 'OK';
		}
		return 'No XCPID initalised';
	}


	public static function maintainAssign($actFrom,$statFrom,$actTo,$statTo,$stream_id) {

			$db = DB::getInstance();
			$sql = "SELECT act_out, status_out, assign FROM ACT_MAPPING_VIEW WHERE act_in = '" . $actFrom . "' AND status_in = '" . $statFrom . "' AND pipeline_id = " . $stream_id;
			$data = $db->query($sql);
			if($data->count()) {
				$return = $data->results();
				$rulesArray = array();
				foreach ($return as $value) {
					if(is_numeric($value->status_out)){
						if($value->status_out == $statTo && $value->act_out == $actTo) {
							return $value->assign;
						}
					} elseif(substr($value->status_out,0,1) == "*") {
						// All available at ACT
						if($value->act_out == $actTo) {
							return $value->assign;
						}
					}
				}
				return flase;
			}
			return flase;
	}

	public static function getAction($actFrom,$statFrom,$actTo,$statTo,$stream_id) {

			$db = DB::getInstance();
			$sql = "SELECT act_out, status_out, action_id FROM ACT_MAPPING_VIEW WHERE act_in = '" . $actFrom . "' AND status_in = '" . $statFrom . "' AND pipeline_id = " . $stream_id;
			$data = $db->query($sql);
			if($data->count()) {
				$return = $data->results();
				$rulesArray = array();
				foreach ($return as $value) {
					if(is_numeric($value->status_out)){
						if($value->status_out == $statTo && $value->act_out == $actTo) {
							return $value->action_id;
						}
					} elseif(substr($value->status_out,0,1) == "*") {
						// All available at ACT
						if($value->act_out == $actTo) {
							return $value->action_id;
						}
					}
				}
				return flase;
			}
			return flase;
	}

	public static function maintainAssignment($act,$stat) {
		$sql = "SELECT assign FROM ACT_MAPPING WHERE act_in = $act AND status_in = $stat";

		$db = DB::getInstance();
		$data = $db->query($sql);

		if($data->first()->assign == 1){
			return true;
		}
		return false;	

	}

	public function moveToActivity($activity = null, $status = null, $user = null, $strict = true, $comment = null) {
			if ($this->getCurrentStatus() == $status && $this->getCurrentActivity() == $activity) {
				throw new Exception("Already there!");
			} else {
				if($strict) {
					if (!$this->canGoToStage($activity . ":" . $status)) {
    					throw new Exception("Not allowed to go to this stage (" . $activity . ":" . $status . ")" );
    					return false;
					}
				}
				$fields = array( 	'XCPID' 	=> $this->xcpid,
									'ACT'		=> $activity,
									'STATUS'	=> $status,
									'DATE'		=> date("Y/m/d H:i:s"). substr((string)microtime(), 1, 3),
									'USER_ID'	=> $user,
									'DATA'		=> $comment
								);	
				if(!$this->_db->insert('ACT_AUDIT', $fields)) {
					throw new Exception("Some database error: " . $this->_db->errorInfo()[2]);			
				}
				if($this->maintainAssign($this->getCurrentActivity(),$this->getCurrentStatus())) {
					$this->claim();
				}
				return 'OK';
			}
	}

	public function setStatus($status) {
		if($this->xcpid) {
			if($activity = $this->nextActivity($status)) {
				$user = new User();
				$userId = $user->data()->id;
				try{

					$this->moveToActivity($this->getCurrentActivity(), $status, $userId, false, null);
					$this->moveToActivity($activity, 0, $userId, false, null);
				} catch(Exception $e) {
					return  $e->getMessage();
				}
				return 'OK';
			}
			return false;
		}
	}

	public static function showAtStage($act, $status){
		$sql = "SELECT *
				FROM mainData
				OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
				LEFT JOIN USERS ON USERS.id = AUDIT.USER_ID
				WHERE AUDIT.ACT = $act and STATUS = $status AND AUDIT.XCPID IS NOT NULL";

		$db = DB::getInstance();
		$data = $db->query($sql);
		return $data->results();
	}

	public function canGoToStage($stage) {
		if($stage){
			foreach ($this->_actRules as $key => $value) {
				if($stage == $key){
					return true;
				}
			}
		}
		return false;
	}

	public static function getFeeds() {
		$sql = "SELECT  [feed_id],[feed_name]
 				FROM [dbo].[FEEDS]";

		$db = DB::getInstance();
		$data = $db->query($sql);
		return $data->results();
	}

	public static function getActivities() {
		$sql = "SELECT [ID],[SHORT_NAME] ,[FULL_NAME] ,[DESCRIPTION]
			  	FROM [dbo].[ACT_DETAIL]";

		$db = DB::getInstance();
		$data = $db->query($sql);
		return $data->results();
	}

	public static function getStreams() {
		$sql = "SELECT[id],[name]
				FROM [dbo].[STREAM_DETAILS]";

		$db = DB::getInstance();
		$data = $db->query($sql);
		return $data->results();
	}

	public static function updateMappingRule($ruleId, $stage, $assign) {
		$stageSplit = Activity::splitStage($stage, ':');
		$fields = array("act_out" => $stageSplit[activity],
						"status_out" => $stageSplit[status],
						"assign" => ($assign ? '1' : '0')
						);
		$db = DB::getInstance();
		if(!$db->update('ACT_MAPPING', $ruleId, 'id', $fields)) {
			throw new Exception($db->errorInfo()[2]);
		}
	}

	public static function addMappingRule($fromStage, $toStage, $assign, $set) {
		echo $fromStage .':' . $toStage .':' . $assign .':' . $set;
		$stageSplitTo = Activity::splitStage($toStage, ':');
		print_r($stageSplitTo);
		$stageSplitFrom = Activity::splitStage($fromStage, ':');
		$fields = array("act_out" => $stageSplitTo[activity],
						"status_out" => $stageSplitTo[status],
						"act_in" => $stageSplitFrom[activity],
						"status_in" => $stageSplitFrom[status],
						"assign" => ($assign == 'true' ? '1' : '0'),
						"set_id" => $set
						);
		print_r($fields);
		$db = DB::getInstance();
		if(!$db->insert('ACT_MAPPING', $fields)) {
			throw new Exception($db->errorInfo()[2]);
		}
	}

	public static function deleteMappingRule($id) {
		$db = DB::getInstance();
		$db->delete( "ACT_MAPPING", array('id','=',$id) );

	}

	public static function updateStageInfo($id, $name, $desc) {
		$fields = array("name" => $name,
						"description" => $desc
						);
		$db = DB::getInstance();
		if(!$db->update('ACT_STATUS_2', $id, 'id', $fields)) {
			throw new Exception($db->errorInfo()[2]);
		}
	}

	public static function showPipelinesForRuleset($ruleSet) {
		if($ruleSet) {
			$sql = "SELECT [pipeline_id]
					FROM [dbo].[ACT_MAPPING_LINK]
					WHERE mapping_set_id = $ruleSet";
			$db = DB::getInstance();
			$data = $db->query($sql);
			$piplines =  $data->results();
			return $piplines;
		}
		return false;
	}

	public static function showRuleSets() {
			$sql = "SELECT DISTINCT set_id
					FROM [dbo].[ACT_MAPPING]";
			$db = DB::getInstance();
			$data = $db->query($sql);
			return $data->results();
	}

	public static function showStages($act = null, $stat = null) {
		$return = array();
		$sql = "SELECT [act] ,[status] ,[name] ,[description], id
				FROM [dbo].[ACT_STATUS_2]";

		if($stat) {
			$sql .= "WHERE status = '$stat'";
		}

		$db = DB::getInstance();
		$data = $db->query($sql);
		$statuses =  $data->results();

		$sql = "SELECT ID, [SHORT_NAME],[FULL_NAME],[DESCRIPTION]
  				FROM [dbo].[ACT_DETAIL]";

		if($act) {
			$sql .= "WHERE id = '$act'";
		}

		$db = DB::getInstance();
		$data = $db->query($sql);
		$activities =  $data->results();

		$sql = "SELECT [act_in],[status_in],[act_out],[status_out],[id],[assign],[set_id]
  				FROM [dbo].[ACT_MAPPING]";

		$db = DB::getInstance();
		$data = $db->query($sql);
		$mappings =  $data->results();
		foreach ($activities as $activity) {
			$return[str_pad($activity->ID, 2, '0', STR_PAD_LEFT)]['INFO'] = $activity;
			foreach ($statuses as $status) {
				if($status->act == $activity->ID) {
					$return[str_pad($activity->ID, 2, '0', STR_PAD_LEFT)]['STATUSES'][$status->status] = $status;
					$rules = array();
					foreach (Activity::showRuleSets() as $ruleSet) {
						$rules[$ruleSet->set_id] = array();
					}
					foreach ($mappings as $mapping) {
						if($mapping->act_in == $status->act && $mapping->status_in == $status->status) {
							$rules[$mapping->set_id][] = array(
												'stage' => $mapping->act_out . ':' . $mapping->status_out,
												'assign' => $mapping->assign,
												'id' => $mapping->id);
						}
					}
					$return[str_pad($activity->ID, 2, '0', STR_PAD_LEFT)]['STATUSES'][$status->status]->rules = $rules;
					unset($rules);
				}
			}
		}
		return $return;
	}

	public static function initRunning() {
		$sql = "SELECT *
				FROM [dbo].[INIT_STATUS]
				WHERE jobName = '00_jobManager'";

		$db = DB::getInstance();
		$data = $db->query($sql);
		$out = $data->first()->data;
		if($out == "OK"){
			return false;
		} else {
			return $data->first()->start_dt;
		}
	}

}