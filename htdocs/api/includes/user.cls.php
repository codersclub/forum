<?php
class user{
	public $id;
	public $login;
	public $group;
	public $email;
	public $joined;
	public $ip;
	public $avatar;
	public $avatar_size;
	public $posts;
	public $icq;
	public $location;
	public $signature;
	public $website;
	public $title;
	public $last_post;
	public $last_visit;
	public $last_act;
	public $messages;
	public $ratting;
	public $points;
	
	
	
	public function __construct(&$user){
		$this -> id			= $user['id'];
		$this -> login		= $user['name'];
		$this -> group		= $user['mgroup'];
		$this -> email		= $user['email'];
		$this -> joined		= $user['joined'];
		$this -> ip			= $user['ip_address'];
		$this -> avatar		= $user['avatar'];
		$this -> avatar_size= $user['avatar_size'];
		$this -> posts		= $user['posts'];
		$this -> icq		= $user['icq_number'];
		$this -> location	= $user['location'];
		$this -> signature	= $user['signature'];
		$this -> website	= $user['website'];
		$this -> title		= $user['title'];
		$this -> last_post	= $user['last_post'];
		$this -> last_visit	= $user['last_visit'];
		$this -> last_act	= $user['last_activity'];
		$this -> messages	= $user['msg_total'];
		$this -> ratting	= $user['ratting'];
		$this -> points		= $user['points'];
	}
}
?>