<?php

namespace Templates;

/**
 * Class Класс шаблона скина
 * @package Templates
 */
abstract class BaseTemplate
{

    /**
     * Возвращает путь к директории шаблона
     * @return string
     */
    public function getDirectory()
    {
        return \Config::get('path.skins') . DIRECTORY_SEPARATOR . $this->getName();
    }

    /**
     * Имя шаблона
     * @return string
     */
    public function getName()
    {
        return substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
    }

    /**
     * Возвращает содержимое враппера
     * @return string
     */
    public function getWrapper()
    {
        ob_start();
        require $this->getDirectory() . DIRECTORY_SEPARATOR . 'wrapper.tpl.php';
        return ob_get_clean();
    }
}
