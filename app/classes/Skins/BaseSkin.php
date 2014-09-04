<?php

namespace Skins;

use \Ibf;
use Templates\BaseTemplate;
use \Templates\Factory as TemplateFactory;

/**
 * Базовый класс скина. По сути - хелпер с хранением содержимого из ibf_skins
 * @package Skins
 */
class BaseSkin implements \ArrayAccess
{
    /** @var  BaseTemplate */
    protected $template;
    /** @var  mixed db info */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->loadTemplate();
    }

    /**
     * Возвращает путь к изображениям
     * @param bool $full True если требуется полный путь, иначе - относительный.
     * @return string
     */
    public function getImagesPath($full = false)
    {
        return $full
            ? Ibf::app()->vars['board_url'] . 'style_images/' . $this->data['img_dir']
            : 'style_images/' . $this->data['img_dir'];
    }

    /**
     * Возвращает путь к css файлам.
     * @return string
     */
    public function getCSSFile()
    {
        //По хорошему надо использовать полный путь, но Сима опять перемудрил
        return 'assets/stylesheets/skins/' . $this->data['css_id'];
    }

    /**
     * Создаёт класс шаблона
     * @throws \Exception
     */
    protected function loadTemplate()
    {
        $this->template = TemplateFactory::create($this->data['template_class']);
    }

    /**
     * геттер для template
     * @return BaseTemplate
     */
    public function getTemplate()
    {
        return $this->template;
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
        return isset($this->data[$offset]);
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
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        } elseif (method_exists($this, $method = 'get' . $offset)) {
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
