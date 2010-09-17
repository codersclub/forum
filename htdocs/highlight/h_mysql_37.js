window['h_mysql']=[
 [
    /^#.*\n/,
    ['<span style=\'color:gray\'><i>',3],
    ['</i></span>',0]
 ],
 [
    /^(\/\*(.|\n)*?\*\/)/i,
    ['',0],
    ['<span style=\'color:gray\'><i>',3],
    ['</i></span>',0]
 ],
 [
    /^(select|insert *?into|delete|update|create *?table|create *?database|drop *?table|drop *?database|alter *?table|flush|grant|priveleges|lock *?table|unlock *?tables|left *?join|right *?join|inner *?join|join|limit|explain)\b/i,
    ['',0],
    ['<span style=\'color:blue\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(distinct|singular|from|where|group( |\n)*?by|order( |\\n)*?by|having|add|drop|change|asc|desc|values|default|primary( |\n)*?key|not( |\n)*?null|in|write|read|left|right)\b/i,
    ['',0],
    ['<span style=\'color:green\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(min|max|avg|sum|count|mod|between|and|or|xor|isnull|interval|ifnull|if|abs|sign|floor|ceiling|round|exp|log|log10|pow|power|pi|cos|sin|tan|acos|asin|atan|atan2|cot|rand|least|greatest|degrees|radians|truncate|ascii|conv|ord|bin|oct|hex|char|concat|length|locate|instr|substring|ltrim|rtrim|trim|replace|insert|lcase|lower|ucase|upper|find_in_set)\b/i,
    ['',0],
    ['<span style=\'color:brown\'>',3],
    ['</span>',0]
 ],
 [
    /^\'(\\.|.)*?(\n|\')/,
    ['<span class=\'movedprefix\'>',3],
    ['</span>',0]
 ],
 [
    /^\"(\\.|.)*?(\n|\")/,
    ['<span class=\'movedprefix\'>',3],
    ['</span>',0]
 ],
 [
    /^(tinyint|smallint|bigint|mediumint|int|integer|float|double|real|decimal|numeric|date|datetime|timestamp|time|year|char|varchar|tinyblob|tinytext|blob|text|mediumblob|mediumtext|longblob|longtext|enum|set)\b/i,
    ['',0],
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^((-|)[0-9]*?([0-9]|\.)[0-9]*?)/,
    ['',0],
    ['<span style=\'color:magenta\'>',3],
    ['</span>',0]
 ],
 [
    /^[0-9a-z_]+/i,
    ['',2]
 ],
 [
    /^(select)([^a-z0-1_](.|\n)*?)(from)([^a-z0-1_])/i,
    ['',0],
    ['<span style=\'color:blue\'><b>',3],
    ['</b></span><span style=\'color:orange\'>',2],
    ['',0],
    ['</span><span style=\'color:green\'><b>',3],
    ['</b></span>',0]
 ]
];
