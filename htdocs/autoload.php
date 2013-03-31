<?php


function _do_autoload($class_name)
{

	$DIRNAME = dirname(__FILE__);
	$fname   = "$DIRNAME/sources/lib/classes/$class_name.class.php";
	if (file_exists($fname))
	{
		require $fname;
		return;
	}

	$fname = "$DIRNAME/sources/lib/classes/traits/$class_name.trait.php";
	if (file_exists($fname))
	{
		require $fname;
		return;
	}

	//load skin class. We can't do it while we don't know which skin we are using
	if (class_exists(Ibf, FALSE) AND Ibf::isApplicationRegistered())
	{
		$skin_id = Ibf::app()->skin_id;
		$fname   = "$DIRNAME/Skin/{$skin_id}/$class_name.php";
		if (file_exists($fname))
		{
			require $fname;
			return;
		}
	}
}

spl_autoload_register('_do_autoload');
