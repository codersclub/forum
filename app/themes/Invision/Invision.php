<?php

namespace Skins\Themes;

/**
 * Class Invision Обработчик для оригинальных тем invision boards forum.
 * Преобразует путь к шаблону в вызов метода соответствующего класса внутри темы.
 * Поиск метода немного расширен по сравнению с обработкой пути в базовой теме для облегчения рефакторинга.
 * Так путь "foo.bar" по порядку преобразуется в
 * 1. skin_foo::bar()
 * 2. skin_foo::renderBar()
 * 3. skin_global::fooBar()
 * 4. skin_global::renderFooBar()
 * Note: Так как в описаниях методов классов шаблонов переменные определены непосредственно, то при вызове рендера важнее
 * порядок передаваемых параметров, чем их имена.
 * @package Skins\Themes
 */
class Invision extends AbstractTheme
{

    /**
     * @param string $path
     * @param mixed $data
     * @return bool|mixed|string
     */
    public function getHtml($path, $data)
    {
        static $paths = [];
        static $classes = [];
        if (!isset($paths[$path])) {
            $args    = explode('.', $path, 2);
            $args[1] = str_replace('.', '_', $args[1]);
            if (!isset($classes[$args[0]])) {
                $classname = 'skin_' . $args[0];
                if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $classname . '.php')) {
                    require __DIR__ . DIRECTORY_SEPARATOR . $classname . '.php';
                    $classes[$args[0]] = new $classname();
                } elseif ($args[0] !== 'global') {
                    return $this->getHtml('global.' . $path, $data);
                } else {
                    return false;
                }
            }

            if (method_exists($classes[$args[0]], $args[1])) {
                $paths[$path] = [$classes[$args[0]], $args[1]];
            } elseif (method_exists($classes[$args[0]], 'render' . $args[1])) {
                $paths[$path] = [$classes[$args[0]], 'render' . $args[1]];
            } else {
                return false;
            }
        }
        return call_user_func_array($paths[$path], $data);
    }
}
