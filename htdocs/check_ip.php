<?php

// Root path
define( 'ROOT_PATH', "./" );

require ROOT_PATH."../conf_global.php";
require ROOT_PATH."sources/functions.php";
$std   = new FUNC;
$sess  = new session();

$INFO['sql_driver'] = !$INFO['sql_driver'] ? 'mySQL' : $INFO['sql_driver'];

$to_require = ROOT_PATH."sources/Drivers/".$INFO['sql_driver'].".php";
require ($to_require);

$DB = new db_driver;

$DB->obj['sql_database']     = $INFO['sql_database'];
$DB->obj['sql_user']         = $INFO['sql_user'];
$DB->obj['sql_pass']         = $INFO['sql_pass'];
$DB->obj['sql_host']         = $INFO['sql_host'];
$DB->obj['sql_charset']      = $INFO['sql_charset'];
$DB->obj['sql_tbl_prefix']   = $INFO['sql_tbl_prefix'];
$DB->obj['debug']            = ($INFO['sql_debug'] == 1) ? $_GET['debug'] : 0;

if ( $DB->connect() )
{
	$ibforums->input = $std->parse_incoming();
	$ibforums->member = $sess->authorise();

	if ( $ibforums->member['id'] )
	{
		echo "Hello, ".$ibforums->member['name'];
	} else
	{
		echo "Hello, guest!";
	}

	$DB->close_db();
}



?>
<form>
IP: <input type=text name=ip>

<input type=submit value=GO>
</form>

<?
$ip = $ibforums->input['ip'];
if($sess->is_ip_banned($ip)) {
  echo "IP ".$ip." banned.";
} else {
  echo "IP ".$ip." NOT banned.";
}
