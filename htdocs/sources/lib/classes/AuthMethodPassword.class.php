<?php

class AuthMethodPassword extends AuthBasic
{

	private $password;

	public function checkInput()
	{
		global $ibforums, $std, $print, $sess;

		$len_u = $std->txt_stripslashes($_POST['UserName']);

		$len_u = preg_replace("/&#([0-9]+);/", "-", $len_u);

		$len_p = $std->txt_stripslashes($_POST['PassWord']);

		$len_p = preg_replace("/&#([0-9]+);/", "-", $len_p);

		//-------------------------------------------------
		// Make sure the username and password were entered
		//-------------------------------------------------

		if ($_POST['UserName'] == "")
		{
			$this->setLastErrorCode('no_username');
			return false;
		}

		if ($_POST['PassWord'] == "")
		{
			$this->setLastErrorCode('pass_blank');
			return false;
		}

		//-------------------------------------------------
		// Check for input length
		//-------------------------------------------------

		if (strlen($len_u) > 32)
		{
			$this->setLastErrorCode('username_long');
			return false;
		}

		if (strlen($len_p) > 32)
		{
			$this->setLastErrorCode('pass_too_long');
			return false;
		}
		$this->setUsername($_POST['UserName']);
		$this->password = $_POST['PassWord'];

		return true;
	}

	public function getFields()
	{
		return array(
			array(
				'type' => 'text',
				'name' => 'UserName'
			),
			array(
				'type' => 'password',
				'name' => 'PassWord'
			),
		);
	}

	public function authenticate()
	{
		global $ibforums, $std, $print, $sess;

		$username = strtolower(str_replace('|', '&#124;', $ibforums->input['UserName']));
		$password = md5($ibforums->input['PassWord']);

		//-------------------------------------------------
		// Attempt to get the user details
		//-------------------------------------------------

		$member = $ibforums->db
			->query("SELECT id, name, mgroup, password FROM ibf_members WHERE LOWER(name)=" . $ibforums->db->quote($username))
			->fetch();

		if (!$member)
		{
			$this->setLastErrorCode('wrong_name');
			return false;
		}

		if (empty($member['id']) or ($member['id'] == ""))
		{
			$this->setLastErrorCode('wrong_name');
			return false;
		}

		if ($member['password'] != $password)
		{
			$this->setLastErrorCode('wrong_pass');
			return false;
		}

		//------------------------------

		if ($ibforums->input['CookieDate'])
		{
			$std->my_setcookie("member_id", $member['id'], 1);
			$std->my_setcookie("pass_hash", $password, 1);
			$std->my_setcookie("auth_method", 'password', 1);
		}

		return $member;

	}

	public function sessionDataIsValid($member)
	{
		global $std;
		return $std->my_getcookie('pass_hash') == $member['password'];

	}

}
