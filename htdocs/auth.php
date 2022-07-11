<?php
global $ibforums;

require __DIR__ . '/../app/bootstrap.php';

$std   = new functions;
$sess  = new session();

$DB = new IBPDO();
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
