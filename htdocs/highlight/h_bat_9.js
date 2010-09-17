window['h_bat']=[
 [
    /^(break|cd|chcp|chdir|cls|copy|ctty|date|del|dir|echo|erase|for|if|lfnfor|lh|loadhigh|lock|md|mkdir|path|pause|prompt|rd|ren|rename|rmdir|set|shift|time|truename|type|unlock|ver|verify|vol)\b/i,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^(call|exit|goto)\b/i,
    ['',0],
    ['<span style=\'color:0000C0\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(aux|com1|com2|com3|com4|con|errorlevel|exist|lpt1|lpt2|lpt3|lpt4|not|nul|off|on|prn)\b/i,
    ['',0],
    ['<span style=\'color:888888\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(\n\s*)(@)/,
    ['',0],
    ['',2],
    ['<span style=\'color:0000C0\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^[a-z0-9~`!#\$%\^&\(\)\-_=\+\[\]\{\};',.][a-z0-9~`!@#\$%\^&\(\)\-_=\+\[\]\{\};',.]*/i,
    ['',2]
 ],
 [
    /^(\n\s*)((rem\s|::).*?)\n/,
    ['',0],
    ['',2],
    ['<span style=\'color:A0A0A0\'>',3],
    ['</span>',0]
 ],
 [
    /^(\n\s*)(:.*)\b/,
    ['',0],
    ['',2],
    ['<span style=\'color:00A800\'>',3],
    ['</span>',0]
 ]
];
