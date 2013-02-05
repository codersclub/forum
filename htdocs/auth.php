<?php

// Root path
define( 'ROOT_PATH', "./" );

require ROOT_PATH."conf_global.php";
require ROOT_PATH."sources/functions.php";
$std   = new FUNC;
$sess  = new session();

$to_require = ROOT_PATH."sources/Drivers/IBPDO.php";
require ($to_require);

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
