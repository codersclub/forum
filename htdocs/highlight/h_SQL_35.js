window['h_SQL']=[
 [
    /^(\/\*(.|\n)*?\*\/)/i,
    ['',0],
    ['<span style=\'color:gray\'><i>',3],
    ['</i></span>',0]
 ],
 [
    /^\-\-.*?(\n|$)/,
    ['<span style=\'color:gray\'><i>',3],
    ['</i></span>',0]
 ],
 [
    /^\'(\\.|.)*?(\n|\')/,
    ['<span class=\'movedprefix\'>',3],
    ['</span>',0]
 ],
 [
    /^(select|from|where|join|left|right|inner|outer|cross|full|group|by|having|on|in|as|insert|into|delete|update|create|alter|set|table|database|drop|index|case|when|then|else|end|with|union|all|any|some|exists|values|distinct|view|tran|transaction|commit|rollback|like|between|asc|desc|revoke|null|key|add|default|for|truncate|clustered)\b/i,
    ['',0],
    ['<span style=\'color:blue\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(avg|count|min|max|sum|substring|cast|isnull|coalesce|exec|isnull|execute|lower|upper|not|or|and)\b/i,
    ['<span style=\'color:deeppink\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(varchar|char|int|bit|date|time|datetime|datetimestamp|text|memo|blob|image|binary|float|decimal|money|currency)\b/i,
    ['<span style=\'color:green\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(declare|while|begin|if|goto|procedure|function|getdate|newid|cursor|fetch|next|prior|with|top|open|close|deallocate|go|print|raiserror)\b/i,
    ['<span style=\'color:navy\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^\@[0-9a-z@]+/i,
    ['<span style=\'color:indigo\'>',3],
    ['</span>',0]
 ],
 [
    /^[0-9a-z_]+/i,
    ['',2]
 ],
 [
    /^\#[0-9a-z#]+/i,
    ['<span style=\'color:darkred\'>',3],
    ['</span>',0]
 ],
 [
    /^\[(\\.|.)*?(\n|\])/,
    ['',2]
 ]
];
