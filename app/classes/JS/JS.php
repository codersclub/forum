<?php

namespace JS;

class JS
{
    const TOP = 'top';
    const HEAD = 'head';
    const BOTTOM = 'bottom';

    protected $config = [];
    protected $raw = [];

    public function addRaw($js, $position = self::TOP, $prepend = false)
    {
        if ($prepend) {
            if (!isset($this->raw[$position])) {
                $this->raw[$position] = [];
            }
            array_unshift($this->raw[$position], $js);
        } else {
            $this->raw[$position][] = $js;
        }
    }

    public function AddInline($code, $position = self::TOP)
    {
        $this->addRaw("<script type='text/javascript'>" . $code . "</script>", $position);
    }

    public function addLocal($file, $prepend = false)
    {
        static $added = [];
        if (isset($added[$file])) {
            return;
        }
        $src = \Ibf::app()->vars['board_url'] . '/html/' . $file;
        $this->addRaw(
            "<script type='text/javascript' src='{$src}?" . \Ibf::app()->vars['client_script_version'] . "'></script>",
            self::HEAD,
            $prepend
        );
        $added[$file] = true;
    }

    public function addExternal($link, $prepend = false)
    {
        static $added = [];
        if (isset($added[$link])) {
            return;
        }
        $this->addRaw("<script src='$link'></script>", self::HEAD, $prepend);
        $added[$link] = true;
    }

    public function __set($name, $value)
    {
        $this->addVariable($name, $value);
    }

    public function addVariable($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function render($what)
    {
        $pre = $what === self::TOP
            ? $this->renderConfig()
            : '';

        return $pre . implode('', (array)$this->raw[$what]);
    }

    protected function renderConfig()
    {
        $output = "<script type='text/javascript'>";
        foreach ($this->config as $name => $data) {
            $output .= sprintf(
                "var %s = %s;\n",
                $name,
                json_encode($data, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE)
            );
        }
        $output .= '</script>';
        return $output;
    }
}
