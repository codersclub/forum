<?php

namespace Skins\Themes;

use Exceptions\MissingTemplateException;

/**
 * Base class for theme.
 * @class AbstractTheme
 * @package Skins\Themes
 */
abstract class AbstractTheme
{
    use \SingletonTrait;

    private $parent = null;

    /**
     * Retrieves result HTML.
     * @param string $path
     * @param mixed $data
     * @return string
     */
    abstract public function getHtml($path, $data);

    /**
     * Returns parent theme name if it exists
     * @return string|null
     */
    protected function getParentThemeName()
    {
        return null;
    }

    /**
     * Main render function
     * @param string $path
     * @param mixed $data
     * @return string|false
     * @throws \Exception
     */
    public function render($path, $data)
    {
        try {
            $output = $this->getHtml($path, $data);
        } catch (MissingTemplateException $e) {
            if ($this->getParent() !== null) {
                $output = $this->getParent()
                    ->render($path, $data);
            } else {
                throw new \Exception('Template ' . $path . ' not found');
            }
        }
        return $output;
    }

    /**
     * Returns theme name.
     * @return string
     */
    public function getName()
    {
        $fullClassName = get_called_class();
        $pos           = strrpos($fullClassName, '\\');
        return $pos === false
            ? $fullClassName
            : substr($fullClassName, $pos + 1);
    }

    /**
     * Theme directory. Doubt about usefulness against the __DIR__ directive.
     * @return string
     */
    public function getDirectory()
    {
        return \Config::get('path.templates') . DIRECTORY_SEPARATOR . $this->getName();
    }

    /**
     * Return parent theme class
     * @return null|AbstractTheme
     * @throws \Exception
     */
    public function getParent()
    {
        if (!$this->parent instanceof AbstractTheme) {
            $name = $this->getParentThemeName();
            if (is_string($name)) {
                $this->parent = Factory::create($name);
            }
        }
        return $this->parent;
    }
}
