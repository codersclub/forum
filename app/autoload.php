<?php
function _do_autoload($class_name)
{
    $DIRNAME = __DIR__ . '/../htdocs';

    //load skin class. We can't do it while we don't know which skin we are using
    if (class_exists('Ibf', false) AND Ibf::isApplicationRegistered() AND !empty(Ibf::app()->skin_id)) {
        $skin_id = Ibf::app()->skin_id;
        $fname = "$DIRNAME/Skin/{$skin_id}/$class_name.php";
        if (file_exists($fname)) {
            require $fname;
            return;
        }
    }
}

spl_autoload_register('_do_autoload');
