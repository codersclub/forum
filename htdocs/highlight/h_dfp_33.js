window['h_dfp']=[
 [
    /^(?:\/\/[^\n]*(?:\n|$)|\/*.*?\*\/)/,
    ['<i><span style=\'color:green\'>',3],
    ['</span></i>',0]
 ],
 [
    /^\/\*(.|\n)*?\*\//,
    ['<i><span style=\'color:green\'>',3],
    ['</span></i>',0]
 ],
 [
    /^("(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*')/,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^\$[a-z_]+[\w]*/i,
    ['<b><span style=\'color:008080\'>',3],
    ['</span></b>',0]
 ],
 [
    /^(?:<\?php|<\?|\?>)/,
    ['<span style=\'color:840204\'>',3],
    ['</span>',0]
 ],
 [
    /^[a-zA-Z_\d]+(?:reset|list|each|int|string|object|array|true|false|Infinity|NaN|undefined|echo|class|extends|use_unit|global|new|__FILE__|null|include_once|include|require|require_once|define|interface|public|private|published|protected|static|varglobal|function|return|var|if|for|new|while|with|break|delete|typeof|case|do|switch|catch|else|in|this|void|continue|finally|instanceof|throw|try|default)\b/,
    ['',2]
 ],
 [
    /^(?:reset|list|each|int|string|object|array|echo|class|extends|use_unit|global|new|__FILE__|include_once|include|require|require_once|define|interface|public|private|published|protected|static|varglobal|function|return|var|if|for|new|while|with|break|delete|typeof|case|do|switch|catch|else|in|this|void|continue|finally|instanceof|throw|try|default)\b/,
    ['<b><span style=\'color:00080\'>',3],
    ['</span></b>',0]
 ],
 [
    /^(?:null|Infinity|NaN|undefined|true|false)\b/,
    ['<span class=\'movedprefix\'>',3],
    ['</span>',0]
 ],
 [
    /^(?:escape|eval|parseInt|parseFloat|unescape|decodeURI|decodeURIComponent|encodeURI|encodeURIComponent|isFinite|isNaN|toString|taint|untaint)\b/,
    ['<b><span style=\'color:204020\'>',3],
    ['</span></b>',0]
 ],
 [
    /^(?:[\]\[)(,:]|\-\>)/,
    ['<b><span style=\'color:800000\'>',3],
    ['</span></b>',0]
 ],
 [
    /^[a-zA-Z_]+(?:\d+)\b/,
    ['',2]
 ],
 [
    /^(?:\d+)\b/,
    ['<b><span style=\'color:blue\'>',3],
    ['</span></b>',0]
 ]
];
