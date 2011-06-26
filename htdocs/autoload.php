<?php


function _do_autoload($class_name) {
	
	$DIRNAME = dirname(__FILE__);
	$fname = "$DIRNAME/sources/lib/classes/$class_name.class.php";
	if (file_exists($fname)) {
		require $fname;
	}
	
}

spl_autoload_register('_do_autoload');
