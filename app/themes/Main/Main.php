<?php

namespace Skins\Themes;

/**
 * Реализация простейшей темы
 * @package Skins\Themes
 */
class Main extends BaseTheme
{

    public function getParentThemeName()
    {
        return 'Invision';
    }

    protected function afterRender($path, &$text)
    {
        if (substr($path, 0, 4) === 'tags') {
            $text = trim($text);
        }
        parent::afterRender($path, $text);
    }

    protected function afterGlobalTime(&$text)
    {
        $text = trim($text);
    }

}
