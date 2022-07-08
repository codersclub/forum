<?php
/**
 * This file is part of Forum package.
 *
 * serafim <nesk@xakep.ru> (24.06.2014 19:36)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('public_path')) {
    function public_path($postfix = '')
    {
        $path = Config::get('path.public');
        if ($postfix) { $path .= '/' . $postfix; }
        return $path;
    }
}

if (!function_exists('app_path')) {
    function app_path($postfix = '')
    {
        $path = Config::get('path.app');
        if ($postfix) { $path .= '/' . $postfix; }
        return $path;
    }
}

if (!function_exists('base_path')) {
    function base_path($postfix = '')
    {
        $path = Config::get('path.base');
        if ($postfix) { $path .= '/' . $postfix; }
        return $path;
    }
}

if (!function_exists('storage_path')) {
    function storage_path($postfix = '')
    {
        $path = Config::get('path.storage');
        if ($postfix) { $path .= '/' . $postfix; }
        return $path;
    }
}

/**
 * Show debug info
 * @param $data
 * @param string $name
 */
function dump($data, $name = '')
{
    $buf = var_export($data, true);

    $buf = str_replace('\\r', '', $buf);
    $buf = preg_replace('/\=\>\s*\n\s*array/s', '=> array', $buf);

    echo '<pre>';

    if ($name) {
        echo $name, '=';
    }

    echo $buf;
    echo "</pre>\n";
}

//------------------------------------------------------------------
function backtrace() {

  $raw = debug_backtrace();
   
  echo "<div><b>BackTrace:</b>\n";
  echo "<table border='1' cellPadding='4'>\n";
  echo "<tr>\n";
  echo "<th>File</th>\n";
  echo "<th>Line</th>\n";
  echo "<th>Function</th>\n";
  echo "<th>Args</th>\n";
  echo "</tr>\n";

  foreach($raw as $entry){
    $args = '';

//DEBUG
//echo '<pre>';
//echo "entry: ";
//print_r($entry);
//echo '</pre>';

    if($entry['function'] != 'backtrace') {
      echo "<tr>\n";
      echo "<td>".$entry['file']."</td>\n";
      echo "<td>".$entry['line']."</td>\n";
      echo "<td>".$entry['function']."</td>\n";

      foreach ($entry['args'] as $a) {
        if (!empty($args)) {
            $args .= ', ';
        }
        switch (gettype($a)) {
        case 'integer':
        case 'double':
            $args .= $a;
            break;
        case 'string':
            $a = htmlspecialchars(substr($a, 0, 64)).((strlen($a) > 64) ? '...' : '');
            $args .= "\"$a\"";
            break;
        case 'array':
            $args .= 'Array('.count($a).')';
            break;
        case 'object':
            $args .= 'Object('.get_class($a).')';
            break;
        case 'resource':
//            $args .= 'Resource('.strstr($a, '#').')';
            $args .= $a;
            break;
        case 'boolean':
            $args .= $a ? 'True' : 'False';
            break;
        case 'NULL':
            $args .= 'Null';
            break;
        default:
            $args .= 'Unknown';
        }
      }

      if(!$args) {
          $args = '&nbsp;';
      }
      echo "<td>".$args."</td>\n";
      echo "</tr>\n";
    }
  }

  echo "</table>\n";
}
