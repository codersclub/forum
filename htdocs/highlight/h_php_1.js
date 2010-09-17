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
    /^\'(\\.|.)*?(\n|\')/,
    ['<span style=\'color:red\'>',3],
    ['</span>',0]
 ],
 [
    /^\$[0-9a-z_]*/i,
    ['<span style=\'color:048284\'>',3],
    ['</span>',0]
 ],
 [
    /^\"(\\.|.)*?(\n|\")/,
    ['<span style=\'color:red\'>',3],
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
    /^(mysql_query)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/manual/ru/function.mysql-query.php\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(mysql_connect)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://http&#092;://www.php.net/manual/ru/function.mysql-connect.php\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(mysql_select_db)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/manual/ru/function.mysql-select-db.php\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(mysql_result)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/manual/ru/function.mysql-result.php\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(session_start)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/manual/ru/function.session-start.php\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(mysql_close)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/manual/ru/function.mysql-close.php\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(getenv)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/manual/ru/function.getenv.php\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(preg_replace)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/manual/ru/function.preg-replace.php\' target=\'_blank\'>',3],
    ['</a>',2]
 ],
 [
    /^(header)([^0-9a-z_]|[^0-9a-z](.|\n)*?[^0-9a-z_])/i,
    ['',0],
    ['<a href=\'http://www.php.net/manual/ru/function.header.php\' target=\'_blank\'>',3],
    ['</a>',2]
 ]
];
