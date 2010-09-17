window['h_bat']=[
 [
    /^((rem\s|::).*?)(\n)/i,
    ['<span style=\'color:606060\'>',3],
    ['</span>',0]
 ],
 [
    /^(%[:\~,\-=0-9a-z_]+?%|%%?(\~([fdpnxsatz]+|(\$[0-9a-z_]+?:)))?[0-9a-z_]|![:\~\-=0-9a-z_]*?!)+/i,
    ['',0],
    ['<span style=\'color:880000\'>',3],
    ['</span>',0]
 ],
 [
    /^(assoc|break|call|cd|chcp|chdir|cls|cmdextversion|color|copy|ctty|date|defined|del|dir|do|echo|else|endlocal|equ|erase|errorlevel|exist|exit|for|ftype|if|geq|goto|gtr|in|lfnfor|leq|lh|loadhigh|lock|lss|md|mkdir|move|neq|not|path|pause|popd|prompt|pushd|rd|ren|rename|rmdir|set|setlocal|shift|start|time|title|truename|type|unlock|ver|verify|vol)\b/i,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^(&|\|\|)/i,
    ['',0],
    ['<span style=\'color:0000C0\'>',3],
    ['</span>',0]
 ],
 [
    /^([1,2]?>(&[1,2])?|\||==|<)/i,
    ['',0],
    ['<span class=\'movedprefix\'>',3],
    ['</span>',0]
 ],
 [
    /^(aux|com1|com2|com3|com4|con|DisableDelayedExpansion|DisableExtensions|EnableDelayedExpansion|EnableExtensions|lpt1|lpt2|lpt3|lpt4|nul|off|on|prn)\b/i,
    ['',0],
    ['<span style=\'color:880088\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(@)\b/,
    ['',0],
    ['<span style=\'color:0000C0\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^[a-z0-9~`!#\$\^\-_=\+\[\]\{\};',][a-z0-9~`!@#\%\^&\(\)\-_=\+\[\]\{\};',.]*/i,
    ['',2]
 ],
 [
    /^(\n\s*)(:.*)\b/,
    ['',0],
    ['',2],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ]
];
