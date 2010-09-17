window['h_js']=[
 [
    /^[^{}(;),.\w"'\/\\]+/,
    ['',2]
 ],
 [
    /^(?:\/\/[^\n]*(?:\n|$)|\/\*.*?\*\/)/,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^\/(?:[^\/\\]|\\.)*\//,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^("(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*')/,
    ['<span style=\'color:FF00FF\'>',3],
    ['</span>',0]
 ],
 [
    /^[;,.}{)(]/,
    ['<span style=\'color:red\'>',3],
    ['</span>',0]
 ],
 [
    /^(?:function|return|var|if|for|new|while|with|break|delete|typeof|case|do|switch|catch|else|in|this|void|continue|finally|instanceof|throw|try|default)\b/,
    ['<span style=\'color:blue\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(?:Array|Boolean|Date|Error|Number|Function|Object|Math|String|Undefined|Null)\b/  ,
    ['<span style=\'color:red\'>',3],
    ['</span>',0]
 ],
 [
    /^(?:[+-]?(?:\d+(?:\.\d)?\d*|0?\.\d+)|[+-]?0[xX][0-9A-Fa-f]+|Infinity|NaN|undefined|true|false|null)\b/,
    ['<span style=\'color:FF00FF\'>',3],
    ['</span>',0]
 ],
 [
    /^(?:escape|eval|parseInt|parseFloat|unescape|decodeURI|decodeURIComponent|encodeURI|encodeURIComponent|isFinite|isNaN|toString|taint|untaint)\b/,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^\w+/,
    ['',2]
 ]
];
