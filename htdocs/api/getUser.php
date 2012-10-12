<?php
error_reporting(E_ALL ^ E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
function_exists("session_start") ? session_start() : NULL;


require_once('includes/error.cls.php');
require_once('includes/user.cls.php');

$error = new error();


ob_start();
if(@$_SESSION['time'] == time() && @$_SESSION['microtime'] <= (microtime() - 0.5)){ $error -> message(4); 
}else{ $_SESSION['time'] = time(); $_SESSION['microtime'] = microtime(); }
	
if(@$_GET['ucode'] != '21232f297a57a5a743894a0e4a801fc3'){ $error -> message(1); }
if(!isset($_GET['login']) || !isset($_GET['password'])){ $error -> message(2); }

define( 'ROOT_PATH', "../" );
require ROOT_PATH."../conf_global.php";
$INFO['sql_driver'] = !$INFO['sql_driver'] ? 'mySQL' : $INFO['sql_driver'];

ini_set('include_path', get_include_path().':'.ROOT_PATH);
$to_require = ROOT_PATH."sources/Drivers/".$INFO['sql_driver'].".php";
require ($to_require);

require ROOT_PATH."sources/functions.php";
$std   = new FUNC;

$DB = new db_driver;

$DB->obj['sql_database']     = $INFO['sql_database'];
$DB->obj['sql_user']         = $INFO['sql_user'];
$DB->obj['sql_pass']         = $INFO['sql_pass'];
$DB->obj['sql_host']         = $INFO['sql_host'];
$DB->obj['sql_tbl_prefix']   = $INFO['sql_tbl_prefix'];
$DB->obj['debug']            = ($INFO['sql_debug'] == 1) ? $_GET['debug'] : 0;

if ( !$DB->connect() )
{
	echo "<h1>Слишком много подключений к серверу. Пожалуйста подождите несколько минут и повторите попытку.</h1>";
	exit;
}

// $db = new db('../../conf_global.php', $error);
// $db -> connect();

$row = $DB -> get_row("SELECT * FROM ibf_members WHERE name = '".$DB->quote(base64_decode($_GET['login'])."' AND password='".$DB->quote(md5(base64_decode($_GET['password'])))."'");
/*
			array(
				'query' => 'select',
				'table' => 'members',
				'where' => 'name="'.mysql_real_escape_string($_GET['login']).'"'.' AND password="'.md5($_GET['password']).'"'
			)
		);
*/

// if(!$db -> there($query)) $error -> message(3);

$user = new user($row);

echo json_encode($user);

ob_end_flush();
