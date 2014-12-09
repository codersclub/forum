<?php

namespace Views;

use Skins\Themes\Factory as ThemesFactory;

class View
{

    private $path;
    private $data;

    /**
     * Main static call
     * @param string $path
     * @param array $data
     * @return View
     */
    public static function make($path, $data = [])
    {
        //the factory will be later.
        return  new static($path, $data);
        //return (new static($path, $data))->render();
    }

    /**
     * Reserved for descendants
     */
    protected function beforeRender()
    {
        //insert your code here
    }

    /**
     * Reserved for descendants
     * @param string $text rendered text
     */
    protected function afterRender(&$text)
    {
        //insert your code here
    }

    /**
     * @return false|string
     * @throws \Exception
     */
    public function render()
    {
        $this->beforeRender();
        $text = ThemesFactory::create(\Ibf::app()->skin->getThemeName())
            ->render($this->path, $this->data);
        $this->afterRender($text);
        return $text;
    }

    public function __toString()
    {
        return (string)$this->render();
    }

    public function __construct($path, $data)
    {
        $this->path = $path;
        $this->data = $data;
    }
}
