<?php
/**
 * @file
 */

namespace Skins\Views;

use Skins\Templates\Factory;

class View
{

    public static function Make($template_path, $data = [])
    {
        return Factory::create(\Ibf::app()->skin->getTemplatesName())->render($template_path, $data);
    }
}
