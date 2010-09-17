window['h_java']=[
 [
    /^(?:\/\/[^\n]*|\/\*(.|\n)*?\*\/)/,
    ['<span style=\'color:gray\'><i>',3],
    ['</i></span>',0]
 ],
 [
    /^("(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*')/,
    ['<span style=\'color:green\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(?:(?:\d+(?:\.\d)?\d*([lL]?|[dD]?|[fF]?)|0?\.\d+([dD]?|[fF]?))|0[xX][0-9A-Fa-f]+|true|false|null|\?)\b/,
    ['<span style=\'color:blue\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^[{}\[\]()]+/,
    ['<span style=\'color:990000\'><span style=\'font-size:10pt;line-height:100%\'>',3],
    ['</span></span>',0]
 ],
 [
    /^[!\*\|\/><+-=:;,.]/,
    ['<span style=\'color:990000\'>',3],
    ['</span>',0]
 ],
 [
    /^(?:abstract|continue|for|new|switch|assert|default|goto|package|synchronized|boolean|do|if|private|this|break|double|implements|protected|throw|byte|else|import|public|throws|case|enum|instanceof|return|transient|catch|extends|int|short|try|char|final|interface|static|void|class|finally|long|strictfp|volatile|const|float|native|super|while)\b/,
    ['<span style=\'color:0000BB\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(?:@\w+)/,
    ['<span style=\'color:3f3fbf\'>',3],
    ['</span>',0]
 ],
 [
    /^\w+/,
    ['',2]
 ]
];
