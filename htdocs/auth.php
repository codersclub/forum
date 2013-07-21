<?php

// Root path
define( 'ROOT_PATH', "./" );

require ROOT_PATH."conf_global.php";
require ROOT_PATH."sources/functions.php";
$std   = new functions;
$sess  = new session();

$DB = new IBPDO($INFO);
try {
	$ibforums->input = $std->parse_incoming();
	$ibforums->member = $sess->authorise();

	if ( $ibforums->member['id'] )
	{
		echo "Hello, ".$ibforums->member['name'];
	} else
	{
		echo "Hello, guest!";
	}
}catch(Exception $e){
	//do some stuff
}
