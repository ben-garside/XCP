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
			$data = $this->_db->query("SELECT act_out, status_out FROM ACT_MAPPING_VIEW WHERE act_in = '" . $this->_currentDetails->activity . "' AND status_in = '" . $this->_currentDetails->status . "' AND pipeline_id = " . $this->_xcpInfo->stream_id);
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

	public function getAllActivities($actId = null) {
		if(!$actId) {
			return $this->_activies;
		} else {
			foreach ($this->_activies as $key => $act) {
				if($act->ID == $actId) {
					return $act;
				}
			}
		}
	}

	public function listStages() {
		$sql = "SELECT ACT_STATUS_2.[act] + ':' + ACT_STATUS_2.[status] stage, ACT_DETAIL.FULL_NAME + ' - ' + ACT_STATUS_2.name description FROM [ACT_STATUS_2] left join [ACT_DETAIL] on [ACT_STATUS_2].act = [ACT_DETAIL].ID order by act, status desc";
		$data = DB::getInstance()->query($sql)->results();
		foreach ($data as $key => $value) {
			$out[$value->stage] = $value->description;
		}
		return $out;
	}

	public function showRoles($actId = null) {
		if($actId) {
			$sql = "SELECT role_id, role_name FROM ACT_ROLE_MAPPING join ROLES on ROLES.id = ACT_ROLE_MAPPING.role_id WHERE ACT_ID = '" . $actId . "'";
			$data = DB::getInstance()->query($sql);		
			$roles = $data->results();
			if($data->count()) {
				foreach ($roles as $key => $value) {
					$return[$value->role_id] = $value->role_name;
				}
			return $return;
			}
		} 
	}

	public function getUsersActivities($userId) {
		$allowedActs = $this->getAllowedActivities($userId);
		foreach ($this->_activies as $key => $value) {
			if(in_array($value->ID, $allowedActs)) {
				$out[] = $value;
			}
		}
		return $out;
	}
 
	public function getAllowedActivities($userId) {
		if($userId) {
			$data = $this->_db->query("SELECT * FROM USER_ACTIVITY WHERE id = " . $userId);
			if($data->count()){
				foreach ($data->results() as $key => $value) {
					$out[] = $value->ACT_ID;
				}
				return $out;
			}
		}
	}



	public function getActRules() {
		return $this->_actRules;
	}

	private function _getCurrentDetails() {
		if($this->xcpid) {
			$data = $this->_db->query("SELECT TOP 1 * FROM ACT_AUDIT_2 WHERE XCPID = '" . $this->xcpid . "' ORDER BY ID DESC");
			if($data->count()){
				$this->_currentDetails = $data->first();
			}
		}
	}

	public function getCurrentActivity() {
		return $this->_currentDetails->activity;
	}

	public function getCurrentStatus() {
		return $this->_currentDetails->status;
	}

	public function getCurrentAuditId() {
		return $this->_currentDetails->id;
	}

	public function getCurrentAll() {
		return $this->_currentDetails;
	}

	public function unAssign() {
		$this->_getCurrentDetails();
		$user = new User();
		$sql = 		"UPDATE [dbo].[ACT_AUDIT_2]
					SET allocatedTo = NULL, allocatedBy = NULL, allocatedOn = NULL
					WHERE ID = (SELECT TOP 1 AUDIT.ID
					FROM mainData
					OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT_2 WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
					WHERE AUDIT.activity = " . $this->getCurrentActivity() . " AND AUDIT.XCPID IS NOT NULL AND XCP_ID = '$this->xcpid')";
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
		$date = date("Y/m/d H:i:s"). substr((string)microtime(), 1, 3);
		$sql = 		"UPDATE [dbo].[ACT_AUDIT_2]
					SET allocatedTo = " . $user->data()->id . ", allocatedBy = " . $user->data()->id . ", allocatedOn = '" . $date . "'
					WHERE ID = (SELECT TOP 1 AUDIT.ID
					FROM mainData
					OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT_2 WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
					WHERE AUDIT.activity = " . $this->getCurrentActivity() . " AND AUDIT.XCPID IS NOT NULL AND XCP_ID = '$this->xcpid')";
		$data = $this->_db->query($sql);
		if(!$data->error()){
			return 'OK';
		} else {
			return 'ERROR';
		}
	}

	public function assign($assignerId) {
		$this->_getCurrentDetails();
		$date = date("Y/m/d H:i:s"). substr((string)microtime(), 1, 3);
		$user = new User();
		$sql = 		"UPDATE [dbo].[ACT_AUDIT_2]
					SET allocatedTo = " . $user->data()->id . ", allocatedBy = " . $assignerId . ", allocatedOn = " . $date . "
					WHERE ID = (SELECT TOP 1 AUDIT.ID
					FROM mainData
					OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT_2 WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
					WHERE AUDIT.activity = " . $this->getCurrentActivity() . " AND AUDIT.XCPID IS NOT NULL AND XCP_ID = '$this->xcpid')";
		$data = $this->_db->query($sql);
		if($data->count()){
			return 'OK';
		} else {
			return 'ERROR';
		}
	}


	public function moveToStage($stage) {
		print $stage;
		if($this->xcpid) {
			$user = new User();
			$userId = $user->data()->id;
			try {
				$this->moveToActivity(Activity::splitStage($stage)[activity], Activity::splitStage($stage)[status], $userId, flase, '');
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
			$sql = "SELECT act_out, status_out, action_id FROM ACT_MAPPING_VIEW WHERE act_in = '" . $actFrom . "' AND status_in = '" . $statFrom . "' AND pipeline_id = " . $stream_id . "order by status_out desc";
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

	public static function getActionType($action) {
		$db = DB::getInstance();
		$sql = "SELECT action_type FROM ACTION_LIST WHERE action_id = $action";
		$data = $db->query($sql);
		return $data->first()->action_type;
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

	public function moveToActivity($activity = null, $status = null, $user = null, $strict = false, $comment = null) {
			if ($this->getCurrentStatus() == $status && $this->getCurrentActivity() == $activity) {
				throw new Exception("Already there!");
			} else {
				
				$date = date("Y/m/d H:i:s"). substr((string)microtime(), 1, 3);
				$fields = array( 	'XCPID' 	=> $this->xcpid,
									'activity'	=> $activity,
									'status'	=> $status,
									'startedOn'	=> $date,
									'startedBy'	=> $user,
									'info'		=> $comment
								);	
				if(!$this->_db->insert('ACT_AUDIT_2', $fields)) {
					throw new Exception("Some database error: " . $this->_db->errorInfo()[2]);			
				} else {
					$sqltoGetId = "SELECT TOP 1 id FROM ACT_AUDIT_2 where activity = $activity AND status = $status AND xcpid = '$this->xcpid' order by id desc";
					$newId = $this->_db->query($sqltoGetId)->first()->id;
					if($this->updateOldAudit($this->getCurrentAuditId(), $newId, $activity, $status, $date, $user)){
						if($this->maintainAssign($this->getCurrentActivity(),$this->getCurrentStatus())) {
							$this->claim();
						}
					}
				}
				return 'OK';
			}
	}

	private function updateOldAudit($oldId, $newId, $newActivity, $newStatus, $date, $user) {
		$sql = "UPDATE ACT_AUDIT_2 SET endedBy = '$user', endedOn = '$date', sentToId = '$newId', sentToActivity = '$newActivity', sentToStatus = '$newStatus' WHERE id = '$oldId'";
		//echo $sql;
		$this->_db->query($sql);
		if(!$this->_db->error()){
			return true;	
		}
		return false;
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
				OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT_2 WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
				LEFT JOIN USERS ON USERS.id = AUDIT.startedBy
				WHERE AUDIT.activity = $act and STATUS = $status AND AUDIT.XCPID IS NOT NULL";

		$db = DB::getInstance();
		$data = $db->query($sql);
		return $data->results();
	}

	private function canGoToStage($stage) {
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

	public static function updateMappingRule($ruleId, $stage, $assign, $action) {
		$stageSplit = Activity::splitStage($stage, ':');
		$fields = array("act_out" => $stageSplit[activity],
						"status_out" => $stageSplit[status],
						"action_id" => $action,
						"assign" => ($assign ? '1' : '0')
						);
		$db = DB::getInstance();
		if(!$db->update('ACT_MAPPING', $ruleId, 'id', $fields)) {
			throw new Exception($db->errorInfo()[2]);
		}
	}

	public static function addMappingRule($fromStage, $toStage, $assign, $set, $action) {
		echo $fromStage .':' . $toStage .':' . $assign .':' . $set;
		$stageSplitTo = Activity::splitStage($toStage, ':');
		print_r($stageSplitTo);
		$stageSplitFrom = Activity::splitStage($fromStage, ':');
		$fields = array("act_out" => $stageSplitTo[activity],
						"status_out" => $stageSplitTo[status],
						"act_in" => $stageSplitFrom[activity],
						"status_in" => $stageSplitFrom[status],
						"action_id" => $action,
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

		$sql = "SELECT [act_in],[status_in],[act_out],[status_out],[id],[assign],[set_id],[action_id]
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
												'action' => $mapping->action_id,
												'activity' => $mapping->act_out,
												'status' => $mapping->status_out,
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

	public static function showItemData($xcpid, $source) {
		$sql = "SELECT *
				FROM $source
				WHERE xcpid = '" . $xcpid ."'";
		$db = DB::getInstance();
		$data = $db->query($sql);

		$res =  $data->results();
		foreach ($res as $key => $value) {
			$out[$value->data_key] = $value->data_value;
		}
		return $out;
	}

	public static function showItemValue($id, $xcpid, $source) {
		$sql = "SELECT *
				FROM $source
				WHERE xcpid = '$xcpid' AND data_key = '$id'";
		$db = DB::getInstance();
		$data = $db->query($sql);

		return $data->first()->data_value;
	}

	public function validateItemData($field, $data) {
		$info = Activity::showFieldData($field);
		if($info->data_required && $data == "" ) {
			return array("status" => "301", "message" => "This is a required field.");
		}elseif(!$info->data_required && $data == ""){
			return true;
		}elseif($info->data_validation) {
			$rule = '/' . $info->data_validation . '/';
			preg_match($rule, $data, $matches);
			if(empty($matches)){
				return array("status" => "301", "message" => $info->data_validation_helper );
			}
		}
		return true;
	}

	public static function listActivities() {
		
		$db = DB::getInstance();
		$sql = "SELECT * FROM [ACT_DETAIL]";
		$data = $db->query($sql);
		$data = $data->results();
		foreach ($data as $key => $activity) {
			$out[] = str_pad($activity->ID, 2, '0', STR_PAD_LEFT);
		}
		return $out;
	}

	public static function listStatuses($activity) {
		$db = DB::getInstance();
		$sql = "SELECT * FROM [ACT_STATUS_2] WHERE act = '$activity'";
		$data = $db->query($sql);
		$data = $data->results();
		if(!empty($data)) {
			$out[] = '*';
			foreach ($data as $key => $activity) {
				$out[] = str_pad($activity->status, 2, '0', STR_PAD_LEFT);
			}
		}
		return $out;
	}

	public static function listActions($action = null) {
		
		$db = DB::getInstance();
		$sql = "SELECT * FROM [ACTION_LIST]";
		$data = $db->query($sql);
		$data = $data->results();
		foreach ($data as $key => $activity) {
			if($action){
				if($action == $activity->action_id) {
					return array(	'id' => $activity->action_id, 
									'name' => $activity->action_name, 
									'title' => $activity->action_title, 
									'description' => $activity->action_description, 
									'type' => $activity->action_type);
				}
			}else{
				$out[] = array(	'id' => $activity->action_id, 
								'name' => $activity->action_name, 
								'title' => $activity->action_title, 
								'description' => $activity->action_description, 
								'type' => $activity->action_type);
			}
		}
		return $out;
	}

	public static function listActionFields($action) {
		
		$db = DB::getInstance();
		$sql = "SELECT * FROM [ACTION_FIELDS] WHERE action_id = $action";
		$data = $db->query($sql);
		$data = $data->results();
		foreach ($data as $key => $fields) {
			foreach ($fields as $field => $value) {
			 	$littleArray[$field] = $value;
			 }
			 $out[] = $littleArray;
		}
		return $out;
	}

	public static function showFieldData($field) {
		$db = DB::getInstance();
		$sql = "SELECT * FROM ACTION_FIELDS WHERE field_name  = '$field'";
		$data = $db->query($sql);
		if($data->count()){
			return $data->first();
		}
		return false;
	}

	public static function updateActivity($actId, $data) {
		if($actId) {
			$db = DB::getInstance();
			if(!$db->update('ACT_DETAIL', $actId, 'ID', $data)) {
				throw new Exception("There was an issue updating the user.");
			}
		}
	}

	public function addActivity($data, $roles) {
			$db = DB::getInstance();
			if(!$db->insert('ACT_DETAIL', $data)) {
				throw new Exception("There was an issue creating the activity.");
			}
			$id = $data['ID'];
			foreach ($roles as $key => $role) {
				Activity::addRole($id, $role);
			}

	}

	public function addRole($actId, $rid) {
		$actId = str_pad($actId, 2, "0", STR_PAD_LEFT);
		$data = DB::getInstance()->query("INSERT INTO [ACT_ROLE_MAPPING] ([ROLE_ID],[ACT_ID]) VALUES($rid,'".str_pad($actId, 2, "0", STR_PAD_LEFT)."')");
		if(!$data->error()){
			return true;
		}
		return false;
	}

	public function removeActivity($actId) {
		$data = DB::getInstance()->delete("ACT_DETAIL", array('ID', '=', $actId));
		if(!$data->error()){
			return true;
		}
		return false;
	}

	public function removeRole($actId, $rid) {
		$sql = "DELETE FROM [ACT_ROLE_MAPPING] WHERE ACT_ID = $actId and ROLE_ID = $rid";
		echo $sql;
		$data = DB::getInstance()->query($sql);
		if(!$data->error()){
			return true;
		}
		return false;
	}

	public function getBatchId($xcpid) {
		$db = DB::getInstance();
		$data = $db->query("SELECT data_value FROM ITEM_DATA WHERE data_key = 'Innodata_Batch_ID' AND xcpid = '$xcpid'");
		if($data->count()){
			return $data->first()->data_value;
		} else {
			return false;
		}
	}

	public static function changeItemData($xcpid, $key, $value, $method, $source, $user, $dataType = null) {

		$db = DB::getInstance();
		$date = date("Y/m/d H:i:s"). substr((string)microtime(), 1, 3);
		switch ($method) {
			case 'update':
				$sql = "UPDATE $source SET [data_value] = '$value', edited_on = '$date', edited_by = $user WHERE xcpid = '$xcpid' and data_key = '$key'";
				break;
			case 'insert':
				$sql = "INSERT INTO $source ([xcpid],[data_key],[data_value],[data_type],[created_on],[created_by],[edited_on],[edited_by])
						VALUES ('$xcpid','$key','$value',NULL,'$date','$user',NULL,NULL)";
				break;
			case 'delete':
				$sql = "DELETE FROM $source WHERE xcpid = '$xcpid' and data_key = '$key'";
				break;
			default:
				return array('status' => '350', 'message' => 'Unknown database method: ' . $method);
				break;
		}
		$db->query($sql);
		if($db->error()){
			return array('status' => '300', 'message' => $db->errorInfo());
		} else {
			return array('status' => '100', 'message' => 'Updated ' . $key);			
		}
	}

	public static function deleteActionField($id) {
		$db = DB::getInstance();
		$db->delete( "ACTION_FIELDS", array('field_id','=',$id) );
	}

	public static function addActionRule($data) {
		$db = DB::getInstance();
		if(!$db->insert('ACTION_FIELDS', $data)) {
			throw new Exception($db->errorInfo()[2]);
		}
	}

	public static function updateActionRule($id, $data) {
		$stageSplit = Activity::splitStage($stage, ':');
		$db = DB::getInstance();
		if(!$db->update('ACTION_FIELDS', $id, 'field_id', $data)) {
			throw new Exception($db->errorInfo()[2]);
		}
	}
	public static function updateActionInfo($id, $action_type, $action_title, $action_name, $action_description) {
		$db = DB::getInstance();
		$db->query("SELECT * FROM ACTION_LIST WHERE action_id = $id");
		if($db->count()){
			//UPDATE
			$sql = "UPDATE ACTION_LIST SET 
						action_type = '$action_type'
						,action_name = '$action_name'
						,action_description = '$action_description'
						,action_title = '$action_title'
						WHERE action_id = $id";
		} else {
			//INSERT
			$sql = "INSERT INTO [dbo].[ACTION_LIST] ([action_id],[action_type],[action_name],[action_description],[action_title])
     					VALUES($id, $action_type,'$action_name','$action_description','$action_title')";
		}
		echo $sql;
		if(!$db->query($sql)) {
			throw new Exception($db->errorInfo()[2]);
		}
	}

	public static function getNewAction() {
		$db = DB::getInstance();
		$db->query("SELECT TOP 1 action_id FROM ACTION_LIST order by action_id desc");
		return $db->first()->action_id + 1;
	}

	public static function addStage($data) {
			$db = DB::getInstance();

			$sql = "SELECT * FROM ACT_STATUS_2 WHERE act = " . $data['act'] . " AND status = " . $data['status'];
			$existanceCheck = $db->query($sql);
			if($existanceCheck->count() == 0) {
				if(!$db->insert('ACT_STATUS_2', $data)) {
					throw new Exception($db->errorInfo()[2]);
				}				
			} else {
				throw new Exception('Already exist.');
			}

	}

	public static function deleteStage($stage) {
		$stage = Activity::splitStage($stage, ',');
		echo $stage['activity'];
		$data = DB::getInstance()->query("DELETE FROM ACT_STATUS_2 WHERE act = '" . $stage['activity'] . "' AND status = '" . $stage['status'] . "'");
		if(!$data->error()){
			$data = DB::getInstance()->query("DELETE FROM ACT_MAPPING WHERE act_in = '" . $stage['activity'] . "' AND status_in = '" . $stage['status'] . "'");
			if(!$data->error()){
				return true;
			}
		}
		return false;

	}

}
?>