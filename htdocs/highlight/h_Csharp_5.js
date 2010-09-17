window['h_Csharp']=[
 [
    /^(\/\/\/)(.*?)(\n)/,
    ['',0],
    ['<span style=\'color:gray\'>',3],
    ['</span><span style=\'color:green\'>',3],
    ['</span>',2]
 ],
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
    /^(abstract|event|new|struct|as|explicit|null|switch|base|extern|object|this|bool|false|operator|throw|break|finally|out|true|byte|fixed|override|try|case|float|params|typeof|catch|or|private|uint|char|foreach|protected|ulong|checked|goto|public|unchecked|class|if|readonly|unsafe|const|implicit|ref|ushort|continue|in|return|using|decimal|int|sbyte|virtual|default|interface|sealed|volatile|delegate|internal|short|void|do|is|sizeof|while|double|lock|stackalloc|else|long|static|enum|namespace|string)\b/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^[a-z_][0-9a-z_]+/i,
    ['',2]
 ],
 [
    /^(#if|#else|#elif|#endif|#define|#undef|#warning|#error|#line|#region|#endregion)\b/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ]
];
