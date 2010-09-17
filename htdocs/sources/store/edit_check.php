<?PHP
if(!defined("ROOT_PATH") && !defined("IN_ACP")) die("<center><b><h1>Access Denied</h1><br>You are may not access this script directly.</b></center>");
    // Start user editable elements below
    if(!defined("ROOT_PATH")) define("ROOT_PATH","./");
	
    // Do not edit ANY thing below this
	
    require ROOT_PATH."conf_global.php";
    if(!class_exists("db_driver")) {
	
	$INFO['sql_driver'] = !$INFO['sql_driver'] ? 'mySQL' : $INFO['sql_driver'];

	$to_require = ROOT_PATH."sources/Drivers/".$INFO['sql_driver'].".php";
	require ($to_require);
	$DB = new db_driver;
	$DB->obj['sql_database']     = $INFO['sql_database'];
	$DB->obj['sql_user']         = $INFO['sql_user'];
	$DB->obj['sql_pass']         = $INFO['sql_pass'];
	$DB->obj['sql_host']         = $INFO['sql_host'];
	$DB->obj['sql_tbl_prefix']   = $INFO['sql_tbl_prefix'];

	$DB->obj['debug']            = ($INFO['sql_debug'] == 1) ? $_GET['debug'] : 0;
		
	// Get a DB connection
		
	$DB->connect();
    }	
    function file_check($file_name,$find,$exact=1) {	
	global $INFO;
	$file = $INFO['base_dir'].$file_name;
	$fp = fopen($file, "r");
	$source = fread($fp, filesize($file));
	fclose($fp);
	if($exact) {
	    if(preg_match("#^".$find."#i",$source)) {
		return true;	
	    }
	} else {
	    if(preg_match("#".$find."#i",$source)) {
		return true;	
	    }		
	}
	return false;
    }
    function table_exist($table) {
	global $DB;
	$DB->query("CHECK TABLE ".$table);
	$type = $DB->fetch_row();
	if($type['Msg_type'] == 'error') {
	    return false;
	}
	return true;
    }
    function row_check($table,$field) {
	global $DB;
	$DB->query("SHOW COLUMNS FROM ".$table);
	while($row = $DB->fetch_row()) {
	    $temp = $row['Field'];
	    if(preg_match("/^$temp$/",$field)) {
		return true;
	    }
	}
	return false;
    }
    if(!class_exists("db_driver")) {
	$DB->close_db();  
    }

?>		