window['h_pas']=[
 [
    /^(asm)()([^0-9a-z_]|[^0-9a-z_](.|\n)*?[^0-9a-z_])(end\b)/i,
    ['',0],
    ['<span style=\'color:green\'><b>',3],
    ['</b></span>',0],
    ['<span style=\'color:green\'>',3],
    ['</span>',0],
    ['<span style=\'color:green\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(absolute|asm|and|array|assembler|begin|case|const|constructor|destructor|div|do|downto|else|end|export|exports|external|far|file|for|forward|function|goto|if|implementation|in|out|index|inherited|inline|interface|interrupt|label|library|mod|name|near|nil|not|object|of|or|packed|private|procedure|program|public|record|repeat|resident|set|shl|shr|string|then|to|type|unit|until|uses|var|virtual|while|with|xor|try|finally|except|message|property|protected|published|override|class|stdcall|overload|dynamic|abstract|default|initialization|finalization|platform|deprecated|cdecl|register|pascal|safecall|automated|as|read|write|resourcestring|reintroduce|raise|packedrecord|nodefault)\b/i,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^[0-9]+/,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^\.[0-9]+/,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^\$[0-9a-f]+/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^\#[0-9]+/i,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^'(''|.)*?'/,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^(\{)(\$[a-z_+-]*)((.|\n)*?\})/i,
    ['',0],
    ['<span style=\'color:navy\'>',3],
    ['<b>',2],
    ['</b>',3],
    ['</span>',0]
 ],
 [
    /^[a-z_][0-9a-z_]+/i,
    ['',2]
 ],
 [
    /^\/\/.*?(?:\n|$)/,
    ['<span style=\'color:navy\'><i>',3],
    ['</i></span>',0]
 ],
 [
    /^\{(.|\n)*?\}/,
    ['<span style=\'color:navy\'><i>',3],
    ['</i></span>',0]
 ],
 [
    /^[^\/]\/\*(.|\n)*?\*\//,
    ['<span style=\'color:navy\'><i>',3],
    ['</i></span>',0]
 ],
 [
    /^\(\*(.|\n)*?\*\)/,
    ['<span style=\'color:navy\'><i>',3],
    ['</i></span>',0]
 ]
];
