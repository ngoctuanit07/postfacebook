<?php
class Validate{
	private $_errors = array(),
			$_db = null;

	public function __construct(){
		$this->_db = DB::getInstance();
	}
	
	public function checkFile($source, $items = array()){
		foreach($items as $item => $rules){
			foreach($rules as $rule => $rule_value){
				$value = $source[$item];
				$item = escape($item);
				$disp_text = $rules['disp_text'];

				if($rule === 'required' && empty($value['tmp_name'])){
					$this->addError("{$disp_text} can not be empty.");
				}else if(!empty($source[$item]['name'])){
					switch ($rule) {
						case 'is_allowed':
							if(in_array(strtolower(substr(strrchr($source[$item]['name'], '.'), 1)), $rules['is_allowed']) === false){
								$allowedExt = implode($rules['is_allowed'],',');
								$this->addError("The file you attempted to upload is not allowed.  Acceptable extensions: ({$allowedExt})");
							}
							break;
						case 'maxsize':
							if($source[$item]['size'] > $rules['maxsize']){
								$maxsize = $rules['maxsize']/1000000; // Convert from byte to MB
								$this->addError("The file you attempted to upload is too large.  {$maxsize}MB is the maximum allowed size for a file!");
							}
							break;
					}
				}
			}
		}
		
		return $this;
	}	
	public function check($source, $items = array()){
		foreach($items as $item => $rules){
			foreach($rules as $rule => $rule_value){
				$value = trim(@$source[$item]);
				$item = escape($item);
				$disp_text = $rules['disp_text'];
				if($rule === 'required' && empty($value)){
					$this->addError("{$disp_text} ".lang('CAN_NOT_BE_EMPTY'));
				}else if(!empty($value)){
					switch ($rule) {
						case 'min':
							if(strlen($value) < $rule_value){
								$this->addError("{$disp_text} ".lang('MUST_BE_MINIMUM_OF')." {$rule_value} ".lang('CHARACTERS').".");
							}
							break;
						case 'max':
							if(strlen($value) > $rule_value){
								$this->addError("{$disp_text} ".lang('MUST_BE_MAXIMUM_OF')." {$rule_value} ".lang('CHARACTERS').".");
							}
							break;
						case 'valid_email':
							if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
								$this->addError("{$value} ".lang('IS_NOT_VALID_EMAIL'));
							}
							break;
						case 'matches':
							if($value != $source[$rule_value]){
								$this->addError("{$disp_text} ".lang('YOU_ENTERED_DID_NOT_MATCH'));
							}
							break;
						case 'unique':
							$check = $this->_db->get($rule_value, array($item, '=', $value));
							if($check->count()){
								$this->addError("{$disp_text} ".lang('HAS_BEEN_TOKEN'));
							}
							break;
						case 'exists':
							$check = $this->_db->get($rule_value, array($item, '=', $value));
							if(!$check->count()){
								$this->addError("{$disp_text} ".lang('NOT_EXISTS'));
							}
							break;
						case 'inArray':
							if(in_array($source[$item], $rules['inArray']) === false){
								$this->addError("{$disp_text} ".lagn('NOT_DEFINED'));
							}
							break;
						case 'regex':
							if(!preg_match($rules['regex'], $value)){
								$this->addError("{$disp_text} must contain only alphanumeric characters and lower case letters.");
							}
							break;
					}
				}
			}
		}
		return $this;
	}

	private function addError($error){
		$this->_errors[] = $error;
	}

	public function errors(){
		return $this->_errors;
	}

	public function passed(){
		if(empty($this->_errors)){
			return true;
		}	
		return false;
	}
	
}
?>