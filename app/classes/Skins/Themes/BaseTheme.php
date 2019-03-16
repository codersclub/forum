<?php

namespace Skins\Themes;

use Exceptions\MissingTemplateException;

/**
 * @class BaseTheme Обработчик темы, основанный на простом подключении html/php файлов
 * Хранит все шаблоны в файлах в директории темы, пути к файлам получаются через замену в пути шаблона точек на
 * разделитель пути. Т.е. шаблону foo.bar соответствует файл theme_path/foo/bar.inc
 * @package Skins\Themes
 */
class BaseTheme extends AbstractTheme
{
    /**
     * @var bool флаг запрета обработки шаблона
     */
    private $skipRendering = false;

    /**
     * Переводит путь к шаблону в имя метода
     * @param string $path
     * @return string
     */
    private function pathToMethodPart($path)
    {
        return str_replace('.', '', $path);
    }

    /**
     * Метод, исполняемый перед обработкой шаблона.
     * Общий для всех. Для предобработки конкретного шаблона лучше использовать метод before{TemplatePath}
     * @param string $path путь к шаблону
     * @param mixed $vars Переданные данные
     */
    protected function beforeRender($path, &$vars)
    {
        $part = 'before' . $this->pathToMethodPart($path);
        //Выполнение метода this->before{TemplatePath}
        if (method_exists($this, $part)) {
            $this->$part($vars);
        }
    }

    /**
     * Хелпер для отмены обработки шаблона. Имеет смысл только в методах предобработки beforeSmth()
     */
    final protected function skipRendering()
    {
        $this->skipRendering = true;
    }

    /**
     * Метод для постобработки результата вывода шаблона
     * @param string $path
     * @param string $text
     */
    protected function afterRender($path, &$text)
    {
        $part = 'after' . $this->pathToMethodPart($path);
        //$this->after{TemplatePath}
        if (method_exists($this, $part)) {
            $this->$part($text);
        }
    }

    /**
     * Основной метод извлечения шаблона
     * @param string $path
     * @param mixed $vars
     * @throws MissingTemplateException
     * @return bool|string
     */
    public function getHtml($path, $vars)
    {
        $f = function () {
            extract(func_get_arg(1));
            ob_start();
            /** @noinspection PhpIncludeInspection */
            include func_get_arg(0);
            return ob_get_clean();
        };

        $file_path = $this->extractPath($path);
        if (!file_exists($file_path)) {
            throw new MissingTemplateException('Template ' . $path . ' does not exist');
        }
        $this->skipRendering = false;
        $this->beforeRender($path, $vars);
        if ($this->skipRendering) {
            $text = '';
        } else {
            $text = $f($file_path, $vars);
            $this->afterRender($path, $text);
        }
        return $text;
    }

    /**
     * Возвращает путь к файлу шаблона по пути шаблона
     * @param $path
     * @return string
     */
    protected function extractPath($path)
    {
        return $this->getDirectory() . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $path) . '.inc';
    }
}
