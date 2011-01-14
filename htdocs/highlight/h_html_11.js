window['h_html']=[
 [
    /^(>)([^<]*)/,
    ['',0],
    ['<span style=\'color:8000FF\'>',3],
    ['</span>',2]
 ],
 [
    /^(<)(!--[\w\W]*?--)(?=>)/,
    ['',0],
    ['<span style=\'color:8000FF\'>',3],
    ['</span><span style=\'color:green\'>',2],
    ['</span>',1]
 ],
 [
    /^(<!\[)(CDATA)(\[)([\w\W]*?)(\]\])(?=>)/,
    ['',0],
    ['<span style=\'color:8000FF\'>',3],
    ['<b>',2],
    ['</b>',3],
    ['</span><span style=\'color:808080\'>',2],
    ['</span><span style=\'color:8000FF\'>',3],
    ['</span>',0]
 ],
 [
    /^(<)()(!DOCTYPE\s*)()([^>]*>)/i,
    ['',0],
    ['<span style=\'color:8000FF\'>',3],
    ['</span>',0],
    ['<b><span style=\'color:8000FF\'>',3],
    ['</span></b>',0],
    ['<span style=\'color:8000FF\'>',3],
    ['</span>',0]
 ],
 [
    /^(<(script|style)(?:"[^"]*"|'[^']*'|[^"'>])*>)([\w\W]*?)()(<\/\2>)/i,
    ['',0],
    ['<span style=\'color:8000FF\'><b>',3],
    ['</b></span>',0],
    ['<span style=\'color:993300\'>',3],
    ['</span>',0],
    ['<span style=\'color:8000FF\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(<)(\??[\w:-]+\s+)([\w:-]+\s*)(?==)/,
    ['',0],
    ['<span style=\'color:8000FF\'>',3],
    ['<b>',2],
    ['</span><span style=\'color:000000\'>',3],
    ['</span></b>',0]
 ],
 [
    /^(<)(\/?[\w:-]+\s*[\/?]?)(?=>)/,
    ['',0],
    ['<span style=\'color:8000FF\'>',3],
    ['<b>',2],
    ['</b></span>',1]
 ],
 [
    /^(=\s*)("[^"]*"|'[^']*'|[^ >]+)(\s+[\w-:]+\s*)(?==)/,
    ['',0],
    ['<span style=\'color:8000FF\'>',3],
    ['</span><span style=\'color:0080FF\'>',2],
    ['</span><b>',3],
    ['</b>',0]
 ],
 [
    /^(=\s*)((?:"[^"]*"|'[^']*'|[^\s>]+)\s*[\/?]?)(?=>)/,
    ['',0],
    ['<span style=\'color:8000FF\'>',3],
    ['</span><span style=\'color:0080FF\'>',2],
    ['</span>',1]
 ],
 [
    /^<!--(.|\n)*?-->/,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^(<)([!\/]?\w+)()([^>]*)(>)/,
    ['',0],
    ['',2],
    ['<span style=\'color:blue\'><span style=\'color:black\'><b>',3],
    ['</b></span>',2],
    ['',3],
    ['</span>',2]
 ]
];
