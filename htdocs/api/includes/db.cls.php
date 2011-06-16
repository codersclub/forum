<?php
class db{
	public $db;
	
	public $user;
	public $password;
	public $database;
	public $host;
	public $prefix;
	
	private $error;
	
	public function __construct($config, &$error){
		$this -> error = $error;
		
		if(file_exists($config)){
			require_once($config);
		}else{
			$this -> error -> message(7);
		}
		
		$this -> host		= $INFO['sql_host'];
		$this -> user		= $INFO['sql_user'];
		$this -> password	= $INFO['sql_pass'];
		$this -> database	= $INFO['sql_database'];
		$this -> prefix		= $INFO['sql_tbl_prefix'];
	}
	
	public function connect(){
		$this -> db = mysql_connect($this -> host, $this -> user, $this -> password)
			or $this -> error -> message(5);
			
		mysql_select_db($this -> database, $this -> db)
			or $this -> error -> message(6);
			
		mysql_query("SET NAMES utf8", $this -> db);
	}
	
	public function query($vars){
		if(!$vars['query'] || $vars['query'] == 'select'){
			$query = 'SELECT * FROM ' . $this->prefix . $vars['table'] . ' WHERE ' . $vars['where'];
			
			if(isset($vars['order'])){	$query .= ' ORDER BY ' . $vars['order']; }
			if(isset($vars['asc'])){	$query .= ' ASC'; }else 
			if(isset($vars['desc'])){	$query .= ' DESC'; }
			if(isset($vars['limit'])){	$query .= ' LIMIT ' . $vars['limit']; }
			
			return mysql_query($query, $this -> db);
		}
	}
	
	public function get_array($query){
		return mysql_fetch_array($query);
	}
	
	public function there($query){
		if(@mysql_num_rows($query)){ return(true); }else{ return(false); }
	}
	
	public function __destruct(){
		@mysql_close($this -> db);
	}
}
?>