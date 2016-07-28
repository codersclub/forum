<?php

namespace Views;

use Skins\Themes\Factory as ThemesFactory;

class BaseView
{

    private $path;
    protected $data;

    /**
     * Reserved for descendants
     */
    protected function beforeRender()
    {
        return true;
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
	global $SKIN;

	if(empty($SKIN)) {
	    $SKIN = \Ibf::app()->skin;
	}

        if ($this->beforeRender()){
            $text = ThemesFactory::create($SKIN->getThemeName())
                ->render($this->path, $this->data);
            $this->afterRender($text);
        }else {
            $text = '';
        }
        return $text;
    }

    public function __toString()
    {
        try {
            return (string)$this->render();
        }catch(\Exception $e){
            \Logs::critical('PHP', get_class($e) . ' raised with message:' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' =>  $e->getTraceAsString()]);
            return '';
        }
    }

    public function __construct($path, $data)
    {
        $this->path = $path;
        $this->data = $data;
    }
}
