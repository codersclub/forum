window['h_php']=[
 [
    /^\/\/.*?\n/,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^\/\*(.|\n)*?\*\//,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^'(\\.|.|\n)*?(')/,
    ['<span class=\'movedprefix\'>',3],
    ['</span>',0]
 ],
 [
    /^\$[0-9a-z_]*/i,
    ['<span style=\'color:048284\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^\"(\\.|.|\n)*?(\")/,
    ['<span class=\'movedprefix\'>',3],
    ['</span>',0]
 ],
 [
    /^(<\?php|<\?)/,
    ['<span style=\'color:840204\'>',3],
    ['</span>',0]
 ],
 [
    /^\?>/,
    ['<span style=\'color:840204\'>',3],
    ['</span>',0]
 ],
 [
    /^<<<("|'|)([a-z0-9_]+)\1(.|\n)*?\n\2/i,
    ['<span class=\'movedprefix\'>',3],
    ['</span>',0]
 ],
 [
    /^(mysql_connect)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/mysql_connect\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(mysql_select_db)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/mysql_select_db\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(mysql_result)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/mysql_result\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(session_start)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/session_start\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(mysql_close)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/mysql_close\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(getenv)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/getenv\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(preg_replace)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/manual/ru/function.preg-replace.php\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^[a-z0-9_](abstract|protected|public|private|var|static|const|final|array|die|echo|empty|exit|eval|include|include_once|isset|list|require|require_once|return|print|unset|declare|if|elseif|else|foreach|as|for|function|return|global|switch|default|try|catch|break|continue|new|clone|use|while|do|goto|throw|namespace|interface|class|extends|implements|self|parent|stdClass|and|xor|or|instanceof|true|false|null)/i,
    ['',2]
 ],
 [
    /^(if|elseif|else|for|foreach|as|function|return|global|switch|default|try|catch|break|continue|new|clone|use|while|do|goto|throw|namespace|interface|class|extends|implements)\b/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^([^a-z0-9_])(default|for|foreach|case)\b/i,
    ['',0],
    ['',2],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(self|parent|stdClass|static::)\b/i,
    ['<span style=\'color:2eb8fc\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(array|die|echo|empty|exit|eval|include|include_once|isset|list|require|require_once|return|print|unset|declare)\b/,
    ['<span style=\'color:7300b3\'>',3],
    ['</span>',0]
 ],
 [
    /^(\s|\n)(xor|and|or|instanceof)\b/i,
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^(true|false|null)\b/i,
    ['<span style=\'color:blue\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(abstract|protected|public|private|var|static|const|final)\b/i,
    ['<span style=\'color:0000aa\'>',3],
    ['</span>',0]
 ],
 [
    /^([^a-z0-9_])(([a-z_][a-z0-9_]*))(\s*:)([^:])/i,
    ['',0],
    ['',2],
    ['<span style=\'color:green\'><b>',3],
    ['</b></span>',0],
    ['<span style=\'color:8f3710\'><b>',3],
    ['</b></span>',2]
 ],
 [
    /^(header)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/header\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(preg_replace)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/preg_replace\' target=\'_blank\'>',3],
    ['</a>',2]
 ]
];
