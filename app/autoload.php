<?php
function _do_autoload($class_name)
{
    //load skin class. We can't do it while we don't know which skin we are using
    //todo delete. use views collection functionality instead
    if (class_exists('Ibf', false) AND Ibf::isApplicationRegistered() AND !empty(Ibf::app()->skin)) {
        $fname = Ibf::app()->skin->getTemplatesPath() . DIRECTORY_SEPARATOR . "$class_name.php";
        if (file_exists($fname)) {
            require $fname;
            return;
        }
    }
}

spl_autoload_register('_do_autoload');
