<?PHP
if (!defined("ROOT_PATH") && !defined("IN_ACP"))
{
	die("<center><b><h1>Access Denied</h1><br>You are may not access this script directly.</b></center>");
}
// Start user editable elements below
if (!defined("ROOT_PATH"))
{
	define("ROOT_PATH", "./");
}

// Do not edit ANY thing below this

require ROOT_PATH . "conf_global.php";
require_once ROOT_PATH . "sources/Drivers/IBPDO.php";
$DB = new IBPDO($INFO);

function file_check($file_name, $find, $exact = 1)
{
	global $INFO;
	$file   = $INFO['base_dir'] . $file_name;
	$fp     = fopen($file, "r");
	$source = fread($fp, filesize($file));
	fclose($fp);
	if ($exact)
	{
		if (preg_match("#^" . $find . "#i", $source))
		{
			return true;
		}
	} else
	{
		if (preg_match("#" . $find . "#i", $source))
		{
			return true;
		}
	}
	return false;
}

function table_exist($table)
{
	global $DB;
	$stmt = $DB->query("CHECK TABLE " . $table);
	$type = $stmt->fetch();
	if ($type['Msg_type'] == 'error')
	{
		return false;
	}
	return true;
}

function row_check($table, $field)
{
	global $DB;
	$stmt = $DB->query("SHOW COLUMNS FROM " . $table);
	while ($row = $stmt->fetch())
	{
		$temp = $row['Field'];
		if (preg_match("/^$temp$/", $field))
		{
			return true;
		}
	}
	return false;
}
