<?php
/**
 * @file
 */

namespace Skins\Templates;

abstract class AbstractTemplate
{
    private $parent = null;
    private $directory;
    private $name;

    abstract public function getHtml($path, $data);

    public function render($path, $data)
    {
//        \Logs::debug(
//            'Debug',
//            'rendering ' . $path,
//            ['dir' => $this->directory, 'name' => $this->name, 'class' => get_called_class()]
//        );
        $output = $this->getHtml($path, $data);
        if ($output === false) {
            if ($this->getParent() !== null) {
                $output = $this->getParent()
                    ->render($path, $data);
            } else {
                throw new \Exception('Template not found');
            }
        }
        return $output;
    }

    public function __construct($name, $parent = null)
    {
        $this->name      = $name;
        $this->parent    = $parent;
        $this->directory = \Config::get('path.templates') . DIRECTORY_SEPARATOR . $name;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function setParent($name)
    {
        $this->parent = $name;
    }

    public function getParent()
    {
        if (is_string($this->parent)) {
            $this->parent = Factory::create($this->parent);
        }
        return $this->parent;
    }

}
