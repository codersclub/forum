<?php

namespace Skins;

/**
 * Скины, основанные на стандартных шаблонах
 * @class InvisionBaseSkin
 * @package Skins
 */
abstract class InvisionBaseSkin extends BaseSkin
{
    /**
     * Возвращает путь к директории шаблона
     * @return string
     */
    public function getTemplatesDirectory()
    {
        return \Config::get('path.templates') . DIRECTORY_SEPARATOR . 'invision';
    }

}
