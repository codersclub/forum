<?php
// если нужно отображать ошибки - раскомментировать:
error_reporting(0);

/* TNX */
class TNX_n
{
        /*
        переменные по умолчанию
        */
        /****************************************/
        var $_timeout_cache = 3600; // 3600 - время для обновления кеша, по умолчанию 3600 секунд, т.е. 1 час
        var $_timeout_down = 3600; // 3600 - время для повторного обращения к tnx, в случае падения сервера, по умолчанию 3600, т.е. 1 час
        var $_timeout_down_error = 60; // максимальное время, для интервала между сбоями при получении ссылок с сервера
        var $_timeout_connect = 5; // таймаут коннекта
        var $_connect_using = 'fsock'; // способ коннекта - curl или fsock
        var $_check_down = false; // проверять, не упал ли поддомен системы. Если упал - не тормозить загрузку страниц на время таймаута
        var $_html_delimiter = '<br>'; // разделитель или текст между ссылками
        var $_encoding = ''; // выбор кодировки вашего сайта. Пусто - win-1251 (по умолчанию). Также возможны: KOI8-U, UTF-8 (необходим модуль iconv на хостинге)
        var $_exceptions = 'PHPSESSID'; // здесь можно написать через пробел части, входящие в урлы для запрещения их индексации системой, в т.ч. из robots.txt. Это урлы, не доступные поисковикам, или не существующие страницы. После индексации не менять.
        var $_forbidden = ''; // запрещенные страницы, через пробел, например нужно запретить http://www.site.ru/index.php пишем '/index.php' и т.д. На страницах типа http://www.site.ru/index.php?id=100 будут отображаться ссылки, чтобы не отображались - используйте exceptions
        /****************************************/

        /*
        далее ничего не менять
        */
        var $_version = '0.2c';
        var $_return_point = 0;
        var $_down_status = 0;
        var $_content = '';

        function TNX_n($login, $cache_dir)
        {
                // проверяем коннекты
                if($this->_connect_using == 'fsock' AND !function_exists('fsockopen'))
                {
                        $this->print_error('Ошибка, fsockopen не поддерживается, попросите хостера включить внешние коннекты или попробуйте CURL');
                        return false;
                }
                if($this->_connect_using == 'curl' AND !function_exists('curl_init'))
                {
                        $this->print_error('Ошибка, CURL не поддерживается, попробуйте fsock.');
                        return false;
                }
                if(!empty($this->_encoding) AND !function_exists("iconv"))
                {
                        $this->print_error('Ошибка, iconv не поддерживается.');
                        return false;
                }
                // осталось со старого варианта, не знаю зачем, но видно надо.
                if (strlen($_SERVER['REQUEST_URI']) > 180)
                {
                        return false;
                }

                if ($_SERVER['REQUEST_URI'] == '')
                {
                        $_SERVER['REQUEST_URI'] = '/';
                }

                if(!empty($this->_exceptions))
                {
                        $exceptions = explode(' ', $this->_exceptions);
                        for ($i=0; $i<sizeof($exceptions); $i++)
                        {
                                if($_SERVER['REQUEST_URI'] == $exceptions[$i]) return false;
                                if($exceptions[$i] == '/' AND preg_match("#^\/index\.\w{1,5}$#", $_SERVER['REQUEST_URI'])) return false;
                                if(strpos($_SERVER['REQUEST_URI'], $exceptions[$i]) !== false) return false;
                        }
                }

                if(!empty($this->_forbidden)) // 21.09.07
                {
                        $forbidden = explode(' ', $this->_forbidden);
                        for ($i=0; $i<sizeof($forbidden); $i++)
                        {
                                if($_SERVER['REQUEST_URI'] == $forbidden[$i]) return false;
                        }
                }

                $login = strtolower($login);
                $this->_host = $login . '.tnx.net';

                $file = base64_encode($_SERVER['REQUEST_URI']);
                $user_pref = substr($login, 0, 2);
                $this->_md5 = md5($file);
                $index = substr($this->_md5, 0, 2);

                $site = str_replace('www.', '', $_SERVER['HTTP_HOST']);

                $this->_path = '/users/' . $user_pref . '/' . $login . '/' . $site. '/' . substr($this->_md5, 0, 1) . '/' . substr($this->_md5, 1, 2) . '/' . $file . '.txt';

                $this->_url = 'http://' . $this->_host . $this->_path;

                $absolute = dirname(__FILE__).'/';

                $site = str_replace('http://', '', $site);
                $site = str_replace('.', '_', $site);

                $this->_cache_file = $absolute . 'cache_' . $site . '_' . $index . '.txt';
                $this->_down_file = $absolute . 'down_' . $site . '.txt';

                /*
                читаем состояние _down_file файла, результат заносим в _down_status
                метод read_down возвращает:
                0 - запросы к сайту разрешены
                time() - старт времени, запросы временно не разрешены
                */
                if($this->_check_down)
                {
                        $this->_down_status = $this->read_down();
                }
                // проверяем, существует ли файл кеша
                if(!is_file($this->_cache_file))
                {
                        // качаем ссылки для определенной страницы
                        $this->_content = $this->get_content();
                        if($this->_content)
                        {
                                /*
                                если ссылки получены, то
                                 - создаем файл _cache_file и заносим в него,
                                   time() создания кеша,
                                   для ориентировки дальнейшего обновления
                                */
                                $this->write_timeout();

                                /*
                                пишем полученные ссылки в кеш _cache_file
                                в виде "_md5|_content\r\n"
                                */
                                $this->write_cache();
                        }
                }
                // если файл кеша существует
                else
                {
                        /*
                        читаем из _cache_file первую строку, время создания кеша.
                        находим время, прошедшее с момента создания кеша
                        */
                        $time = time() - $this->read_timeout();

                        // проверяем, нужно ли обновить кеш
                        if($time > $this->_timeout_cache)
                        {
                                // качаем ссылки для определенной страницы
                                $this->_content = $this->get_content();
                                if($this->_content)
                                {
                                        /*
                                        если ссылки получены, то
                                        - обнуляем файл _cache_file и заносим в него,
                                          time() обновления кеша,
                                          для ориентировки дальнейшего обновления
                                        */
                                        $this->write_timeout();
                                        // пишем полученные ссылки
                                        $this->write_cache();
                                }
                        }

                        /*
                        если обновлять кеш не нужно или же _content == false
                        т.е. метод get_content() вернул false и ссылок не получили, то:
                        */
                        if($time < $this->_timeout_cache OR isset($this->_content))
                        {
                                // пробуем найти по хешу _md5 ссылки для заданной страницы
                                $this->_content = $this->read_cache();
                                if(!$this->_content)
                                {
                                        // если read_cache() вернул false
                                        // пробуем скачать ссылки с tnx
                                        $this->_content = $this->get_content();
                                        if($this->_content)
                                        {
                                                /*
                                                если ссылки получены, то
                                                пишем их в кеш
                                                */
                                                $this->write_cache();
                                        }
                                }
                        }
                }
                // очищаем кеш состояния файлов
                clearstatcache();

                if($this->_content !== false)
                {
                        $this->_content_array = explode('<br>', $this->_content);
                        for ($i=0; $i<sizeof($this->_content_array); $i++)
                        {
                                $this->_content_array[$i] = trim($this->_content_array[$i]);
                        }
                }

        }

        // Выводим ссылки
        function show_link($num = false)
        {
                // проверяем есть ли массив ссылок у нас
                if(!isset($this->_content_array))
                {
                        return false;
                }

                $links = '';

                // подсчитываем количество ссылок в массиве
                if(!isset($this->_content_array_count)){$this->_content_array_count = sizeof($this->_content_array);}
                if($this->_return_point >= $this->_content_array_count)
                {
                        return false;
                }
                // если выводим все ссылки или указанное количество ссылок, больше чем их на самом деле
                if($num === false OR $num >= $this->_content_array_count)
                {
                        for ($i = $this->_return_point; $i < $this->_content_array_count; $i++)
                        {
                                $links .= $this->_content_array[$i] . $this->_html_delimiter;
                        }
                        $this->_return_point += $this->_content_array_count;
                }
                else
                {
                        // если все ссылки уже были выведены, то прекращаем работу
                        if($this->_return_point + $num > $this->_content_array_count)
                        {
                                return false;
                        }

                        for ($i = $this->_return_point; $i < $num + $this->_return_point; $i++)
                        {
                                $links .= $this->_content_array[$i] . $this->_html_delimiter;
                        }

                        // увеличиваем поинт отсчета ссылок
                        $this->_return_point += $num;
                }
                return (!empty($this->_encoding)) ? iconv("windows-1251", $this->_encoding, $links) : $links;
        }

        // функция получения ссылок
        function get_content()
        {
                /*
                проверка в дауне ли сервер из файла _down_file
                0 - сервер рабочий
                */
                if($this->_down_status != 0)
                {
                        /*
                        проверяем таймаут, если указанное время не кончилось,
                        то ссылки не качаем
                        */
                        if(time() - $this->_down_status <= $this->_timeout_down)
                        {
                                return false;
                        }
                        else
                        {
                                // если кончилось обнуляем _down_file и пробуем скачать ссылки
                                $this->clean_down();
                        }
                }

                // указываем свой user agent, чтоб по логам видеть, кто и что запрашивает
                $user_agent = 'TNX_n PHP ' . $this->_version;

                $page = '';

                if ($this->_connect_using == 'curl' OR ($this->_connect_using == '' AND function_exists('curl_init')))
                {
                        // пробуем забрать ссылки курлом
                        $c = curl_init($this->_url);
                        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $this->_timeout_connect);
                        curl_setopt($c, CURLOPT_HEADER, false);
                        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($c, CURLOPT_TIMEOUT, $this->_timeout_connect);
                        curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
                        $page = curl_exec($c);

                        // проверяем все ли прошло гладко, получили ли ссылки, нормальные ответы 200 и 404
                        if(curl_error($c) OR (curl_getinfo($c, CURLINFO_HTTP_CODE) != '200' AND curl_getinfo($c, CURLINFO_HTTP_CODE) != '404') OR strpos($page, 'fsockopen') !== false)
                        {
                                curl_close($c);

                                $this->check_down();
                                return false;
                        }
                        curl_close($c);
                }
                elseif($this->_connect_using == 'fsock')
                {
                        $buff = '';
                        $fp = @fsockopen($this->_host, 80, $errno, $errstr, $this->_timeout_connect);
                        if ($fp)
                        {
                                fputs($fp, "GET " . $this->_path . " HTTP/1.0\r\n");
                                fputs($fp, "Host: " . $this->_host . "\r\n");
                                fputs($fp, "User-Agent: " . $user_agent . "\r\n");
                                fputs($fp, "Connection: Close\r\n\r\n");

                                stream_set_blocking($fp, true);
                                stream_set_timeout($fp, $this->_timeout_connect);
                                $info = stream_get_meta_data($fp);

                                while ((!feof($fp)) AND (!$info['timed_out']))
                                {
                                        $buff .= fgets($fp, 4096);
                                        $info = stream_get_meta_data($fp);
                                }
                                fclose($fp);

                                if ($info['timed_out']) return false;

                                $page = explode("\r\n\r\n", $buff);
                                $page = $page[1];
                                if((!preg_match("#^HTTP/1\.\d 200$#", substr($buff, 0, 12)) AND !preg_match("#^HTTP/1\.\d 404$#", substr($buff, 0, 12))) OR $errno!=0 OR strpos($page, 'fsockopen') !== false)
                                {
                                        $this->check_down();
                                        return false;
                                }
                        }
                }
                // если у нас 404
                if(strpos($page, '404 Not Found'))
                {
                        return '';
                }

                return $page;
        }

        // читаем первую строку _down_file файла
        function read_down()
        {
                if (!is_file($this->_down_file))
                {
                        $this->clean_down();
                        clearstatcache();
                        return 0;
                }

                $fp = fopen($this->_down_file, "rb");

                if ($fp)
                {
                        flock($fp, LOCK_SH);
                        $flag = (int)fgets($fp, 11);
                        flock($fp, LOCK_UN);
                        fclose($fp);
                        return $flag;
                }
                return $this->print_error('Не могу считать данные из файла: ' . $this->_down_file);
        }

        function clean_down ($str = 0)
        {
                $fp = fopen($this->_down_file, "wb+");

                if ($fp)
                {
                        flock($fp, LOCK_EX);
                        fwrite($fp, $str . "\r\n");
                        flock($fp, LOCK_UN);
                        fclose($fp);
                        return true;
                }
                return $this->print_error('Не могу считать данные из файла: ' . $this->_down_file);
        }

        function read_timeout()
        {
                $fp = fopen($this->_cache_file, "rb");

                if ($fp)
                {
                        flock($fp, LOCK_SH);
                        $timeout = (int)fgets($fp, 11);
                        flock($fp, LOCK_UN);
                        fclose($fp);
                        return $timeout;
                }
                return $this->print_error('Не могу считать данные из файла: ' . $this->_cache_file);
        }

        /*down*/
        function write_down()
        {
                $fp = fopen($this->_down_file, "ab+");

                if ($fp)
                {
                        flock($fp, LOCK_EX);
                        fwrite($fp, time() . "\r\n");
                        flock($fp, LOCK_UN);
                        fclose($fp);
                        return true;
                }
                return $this->print_error('Не могу записать данные в файл: ' . $this->_down_file);

        }

        /*down*/
        function down_filesize()
        {
                $size = filesize($this->_down_file);
                clearstatcache();
                return $size;
        }

        /*cache*/
        function write_timeout()
        {
                $fp = fopen($this->_cache_file, "wb+");

                if ($fp)
                {
                        flock($fp, LOCK_EX);
                        fwrite($fp, time() . "\r\n");
                        flock($fp, LOCK_UN);
                        fclose($fp);
                        return true;
                }
                return $this->print_error('Не могу записать данные в файл: ' . $this->_cache_file);
        }
        /*cache*/
        function write_cache($flag = "ab+")
        {
                if($this->_content === false)
                {
                        return false;
                }

                $fp = fopen($this->_cache_file, $flag);

                if ($fp)
                {
                        flock($fp, LOCK_EX);
                        fwrite($fp, $this->_md5 . '|' . $this->_content . "\r\n");
                        flock($fp, LOCK_UN);
                        fclose($fp);
                        return true;
                }
                return $this->print_error('Не могу записать данные в файл: ' . $this->_cache_file);
        }
        /*cache*/
        function read_cache()
        {
                $fp = fopen($this->_cache_file, "rb");

                if ($fp)
                {
                        flock($fp, LOCK_SH);
                        fseek($fp, 11);
                        while (!feof($fp))
                        {
                                 $buffer = fgets($fp);
                                 if (substr($buffer, 0, 32) == $this->_md5)
                                 {
                                         flock($fp, LOCK_UN);
                                         fclose($fp);
                                         return substr($buffer, 33);
                                 }
                        }
                        flock($fp, LOCK_UN);
                        fclose($fp);
                        return false;
                }
                return $this->print_error('Не могу считать данные из файла: ' . $this->_cache_file);
        }

        function check_down()
        {
                if(!$this->_check_down)
                {
                        return false;
                }
                /*
                если ссылок не получили, то
                пишем в _down_file время сбоя
                */
                $this->write_down();

                /*
                в файл _down_file заносится 3 времени сбоев (неудачных обращений к серверу),
                которые случились последовательно, после чего файл занимает 39 байт,
                проверяем размер файла
                */
                if ($this->down_filesize() >= 39)
                {
                        /*
                        если уже было три неудачные попытки, то
                        проверяем временные интервалы между ними
                        */

                        // получили массив $file с ключами 1-3 (время каждого сбоя)
                        $file = file($this->_down_file);
                        for ($i=1; $i<sizeof($file); $i++)
                        {
                                $file[$i] = (int)trim($file[$i]);
                        }

                        // вычисляем среднее время интервалов между 3-мя сбоями
                        $time_error = (($file[3]-$file[2]) + ($file[2]-$file[1])) / 2;

                        // если среднее время меньше допустимой нормы (_timeout_down_error), то
                        if ($time_error <= $this->_timeout_down_error)
                        {
                                /*
                                обнуляем файл _down_file и пишем в него время
                                зафиксировав время падения сервера
                                */
                                $this->clean_down(time());
                        }
                        else
                        {       // если же время в допустимой норме, то просто обновляем в 0
                                $this->clean_down();
                        }
                }
        }

        function print_error($str)
        {
                // echo date("Y-m-d G:i:s") . ' - ' . $str . "<br>\r\n";
        }
}
?>
