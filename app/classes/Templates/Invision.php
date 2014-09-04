<?php
/**
 * @file
 */

namespace Templates;

/**
 * Класс шаблона
 * @package Templates
 */
class Invision extends BaseTemplate
{

    public function getDirectory()
    {
        return \Config::get('path.skins') . DIRECTORY_SEPARATOR . 's1';
    }

}
