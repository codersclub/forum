<?php

namespace Skins;

use \Config;

/**
 * Базовый класс скина. По сути - хелпер с хранением содержимого из ibf_skins
 * @package Skins
 */
abstract class BaseSkinManager implements \ArrayAccess
{
    use \Getters;

    public function __construct()
    {
    }

    /**
     * @return string Наименование скина
     */
    abstract public function getName();

    /**
     * @return int Id набора макросов в бд
     */
    abstract public function getMacroId();

    /**
     * @return string Путь к файлу css
     */
    abstract public function getCSSFile();

    /**
     * @return string Путь к директории с изображениями
     */
    abstract public function getImagesPath();

    /**
     * @return int Идентификатор скина
     */
    abstract public function getId();

    /**
     * Возвращает имя темы
     * @return string
     */
    abstract public function getThemeName();

    public function isHidden()
    {
        return in_array($this->getId(), \Config::get('app.skins.hidden', []));
    }

    public function getMacroValues()
    {
        return require \Config::get('path.data') . '/skin_macro/' . $this->getMacroId() . '.php';
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return method_exists($this, 'get' . $offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (method_exists($this, $method = 'get' . $offset)) {
            return $this->$method();
        } else {
            return null; //todo throw new exception?
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception('Skin info is read only');
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \Exception('Skin info is read only');
    }
}
