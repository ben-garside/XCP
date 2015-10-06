<?php
class Validate {
	private $_passed = false,
			$_errors = array(),
			$_db = null;

	public function __construct(){
		$this->_db = DB::getinstance();
	}

	public function check($source, $items = array()) {
		foreach ($items as $item => $rules) {
			foreach($rules as $rule => $rule_value) {

				$value = trim($source[$item]);
				$item = escape($item);

				if($rule === 'required' && empty($value)) {
					$this->addError("{$item} is required!");
				} else if(!empty($value)) {
					switch($rule) {
						case 'min':
							if(strlen($value) < $rule_value) {
								$this->addError("{$item} must be a minimum of {$rule_value} characters.");
							}
						break;
						case 'max':
							if(strlen($value) > $rule_value) {
								$this->addError("{$item} must be a maximum of {$rule_value} characters.");
							}
						break;
						case 'matches':
							if($value != $source[$rule_value]) {
								$this->addError("{$rule_value} must match {$item}");
							}
						break;
						case 'unique':
							$check = $this->_db->get('users', array($item, '=', $value));
							if($check->count()) {
								$this->addError("{$item} alrady exists");
							}
						break;
						case 'alpha':
							if(is_numeric($value)) {
								$this->addError("{$item} can not be just a number.");
							}
						break;
						case 'lookup':
							if($value !== Config::get('lookup/' . $rule_value)) {
								$this->addError("{$item} is incorrect.");
							}
						break;
					}
				}

			}
		}

		if(empty($this->_errors)) {
			$this->_passed = true;
		}

		return $this;
	}

	private function addError($error) {
		$this->_errors[] = $error;
	}

	public function errors() {
		return $this->_errors;
	}

	public function passed() {
		return $this->_passed;
	}
}