<?
if($Action)
{
	include "IPBOfflineClient.php";
}
else
{
	if(!$Form)
		$Form = "getposts";

	include $Form . "_form.php";
}
