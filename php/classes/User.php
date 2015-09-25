<?php
class User {
	private $_db,
			$_data,
			$_sessionName,
			$_cookieName,
			$_isLoggedIn,
			$_guests;

	public function __construct($user = null) {
		$this->_db = DB::getInstance();
		$this->_sessionName = Config::get('session/session_name');
		$this->_cookieName = Config::get('remember/cookie_name');


		if (!$user) {
			if(Session::exists($this->_sessionName)) {
				$user = Session::get($this->_sessionName);

				if($this->find($user)) {
					$this->_isLoggedIn = true;
				} else {
					// something...
				}
			}
		} else {
			$this->find($user);
		}
	}

	public function update($fields = array(), $id = null) {

		if(!$id && $this->isLoggedIn()) {
			$id = $this->data()->id;
		}
		if(!$this->_db->update('users', $id, 'id', $fields)) {
			throw new Exception("There was an issue updating the user.");
		}
	}

	public function create($fields = array()) {
		if(!$this->_db->insert('users', $fields)) {
			throw new Exception($this->_db->errorInfo());
		}
	}

	public function delete() {
		$id = $this->data()->id;
		$this->_db->delete('users', array('id','=',$id));
	}

	public function find($user = null) {
		if($user) {
			$field = (is_numeric($user)) ? 'id' : 'username';
			$data = $this->_db->get('users', array( $field, '=', $user));

			if($data->count()) {
				$this->_data = $data->first();
				return true;
			}
		}
		return false;
	}

	public function login($username = null, $password = null, $remember = false) {

		if(!$username && !$password && $this->exists()) {
			Session::put($this->_sessionName, $this->data()->id);
		} else {
			$user = $this->find($username);
			if($user) {
				if($this->data()->password === Hash::make($password, $this->data()->salt)) {
					Session::put($this->_sessionName, $this->data()->id);
					$this->update(array('lastLogin' => date('Y-m-d H:i:s')), $this->data()->id);
					if($remember) {
						$hash = Hash::unique();
						$hashCheck = $this->_db->get('users_session', array('user_id', '=', $this->data()->id));

						if(!$hashCheck->count()) {
							$this->_db->insert('users_session', array(
								'user_id' => $this->data()->id,
								'hash' => $hash
							));
						} else {
							$hash = $hashCheck->first()->hash;
						}
						Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
					}
					return true;
				}
			}
		}
		return false;
	}

	public function hasPermission($key) {

		$group = $this->_db->get('groups', array('id', '=', $this->data()->group_id));
		//print_r($group);
		if($group->count()) {
			$permissions = json_decode($group->first()->permissions, true);
			//print_r($permissions);
			if($permissions[$key] == true) {
				return true;
			}
		}
		return false;
	}

	public function inRole($roleId) {
		if(!is_numeric($roleId)) {
			//Get role ID
			$data = $this->_db->get('ROLES', array('role_name', '=', $roleId));
			if(!$roleId = $data->first()->ID){
				return false;
			}
		}
		$data = $this->_db->get('ROLE_USER_MAPPING', array('user_id', '=', $this->data()->id));
		$roles = $data->results();
		if($data->count()) {
			foreach ($roles as $key => $role) {
				if($role->role_id == $roleId) { return true;	}			
			}
			return false;
		}
		return false;
	}


	public function exists() {
		return (!empty($this->_data)) ? true : false;
	}

	public function logout() {

		$this->_db->delete('users_session', array('user_id', '=', $this->data()->id));

		Session::delete($this->_sessionName);
		Cookie::delete($this->_cookieName);
	}

	public function data() {
		return $this->_data;
	}

	public function isLoggedIn() {
		return $this->_isLoggedIn;
	}

	public function group() {
		
		$data = $this->_db->get('groups', array('id', '=', $this->data()->group_id));
		
		if($data->count()){
			$group = $data->first();
			return $group->name;
		}
	}

	public function listUsers($id = null) {
		if($id){
			$data = DB::getInstance()->query("SELECT * FROM users where id = $id");
			if($data->count()) {
				return $data->first();
			}
		} else {
			$data = DB::getInstance()->query("SELECT * FROM users");
			if($data->count()) {
				return $data->results();
			}
		}
	}

	public function showRoles($userId = null) {
		if($userId){
			$data = DB::getInstance()->query("SELECT role_id, role_name FROM [ROLE_USER_MAPPING] join ROLES on ROLES.id = ROLE_USER_MAPPING.role_id WHERE user_id = $userId");		
		} else {
			$data = DB::getInstance()->query("SELECT id role_id, role_name FROM ROLES");		
		}
		$roles = $data->results();
		if($data->count()) {
			foreach ($roles as $key => $value) {
				$return[$value->role_id] = $value->role_name;
			}
		return $return;
		}
	}

	public function addToRole($uid, $rid) {
		$data = DB::getInstance()->query("INSERT INTO [dbo].[ROLE_USER_MAPPING] ([role_id],[user_id]) VALUES($rid,$uid)");
		if(!$data->error()){
			return true;
		}
		return false;
	}

	public function removeFromRole($uid, $rid) {
		$sql = "DELETE FROM [dbo].[ROLE_USER_MAPPING] WHERE user_id = $uid and role_id = $rid";
		echo $sql;
		$data = DB::getInstance()->query($sql);
		if(!$data->error()){
			return true;
		}
		return false;
	}

}