<?php

namespace Skins\Views;

use Skins\BaseSkin;

class Collection implements \ArrayAccess
{
    protected $owner;
    private $loaded;

    public function __construct(BaseSkin $owner)
    {
        $this->owner = $owner;
    }

    public function getView($name)
    {
        if (!isset($this->loaded[$name])) {
            $cname = $this->nameToClassName($name);
            $this->readClassFile($cname);
            $this->loaded[$name] = new $cname();
        }
        return $this->loaded[$name];
    }

    /**
     * @param $class_name
     */
    protected function readClassFile($class_name) {
        $path = $this->getFileFromClassName($class_name);
        if (file_exists($path)) {
            require_once $path;
        }
    }

    protected function getFileFromClassName($class_name){
        return $this->owner->getViewsDirectory() . DIRECTORY_SEPARATOR . "$class_name.php";
    }

    protected function nameToClassName($name)
    {
        return 'skin_' . $name;
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
        return file_exists($this->getFileFromClassName($this->nameToClassName($offset)));
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
        return $this->getView($offset);
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
     * @throws \Exception
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception('Wrong use');
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @throws \Exception
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \Exception('Wrong use');
    }
}
