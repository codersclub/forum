<?php

abstract class AuthBasic {
	
	private $lastErrorCode = '';
	private $username;
	
	
	abstract function checkInput();
	
	abstract function authenticate();
	
	
	function lastErrorCode() {
		return $this->lastErrorCode;
	}
	
	function setLastErrorCode($val) {
		$this->lastErrorCode = $val;
	}
	
	function setUsername($val) {
		$this->username = $val;
	}
	
	function username() {
		return $this->username;
	}
	
	
    /**
     * 
     * @return AuthBasic;
     */
	public static function getAuthObject($type = NULL) {
		
		global $std, $ibforums;
		
		if (!$type) {
			$type = $ibforums->input["auth_method"];
		}
		
		if (!$type) {
			$type = $std->my_getcookie("auth_method");
		}
		
		if (!$type) {
			$type = 'password';
		}
		
		switch ($type) {
			case 'password':
				return new AuthMethodPassword;
			case 'openid':
				return new AuthMethodOpenId;
			default:
				$std->Error( array('MSG'=>'unknown_auth_method'));
		}
	}
	
	public static function checkSessionDataIsValid($member) {
		return self::getAuthObject()->sessionDataIsValid($member);
	}
	
}

