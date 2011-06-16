<?php
class error{
	public $num;
	public $error;
	
	private $error_msg = array(
				'undefined error',
				'error authentication key',
				'wrong number of parameters',
				'not the correct data access',
				'too many connections',
				'database connection error',
				'database select error',
				'can\'t find configuraton file in database class'
			);
	
	function message($num){
		
		$this -> num = $num;
		
		if(isset($this -> error_msg[$num])){
			$this -> error = $this -> error_msg[$num];
		}else{
			$this -> error = $this -> error_msg[0];
		}
		
		echo json_encode($this);
		
		ob_end_flush();
		exit();
	}
}
?>