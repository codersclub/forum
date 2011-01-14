window['h_bash']=[
 [
    /^#!.*?(?:\n|$)/,
    ['<span style=\'color:3fa2ff\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^#.*?(?:\n|$)/,
    ['<span style=\'color:3fefff\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(")()((?:\\"|[^"])*?)()(")/,
    ['',0],
    ['<span style=\'color:ffff54\'>',3],
    ['</span>',0],
    ['<span style=\'color:ff54ff\'><b>',3],
    ['</b></span>',0],
    ['<span style=\'color:ffff54\'>',3],
    ['</span>',0]
 ],
 [
    /^(')()([^']*)()(')/,
    ['',0],
    ['<span style=\'color:ffff54\'>',3],
    ['</span>',0],
    ['<span style=\'color:ff54ff\'><b>',3],
    ['</b></span>',0],
    ['<span style=\'color:ffff54\'>',3],
    ['</span>',0]
 ],
 [
    /^\$(?:[\w\d_]+|#|@|\?|!|\*|{.+?})/,
    ['<span style=\'color:3f4fff\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^[\w\d_](alias|bg|bind|break|builtin|caller|case|command|compgen|complete|continue|declare|dirs|disown|do|done|elif|else|enable|esac|eval|exec|exit|export|false|fc|fg|fi|for|getopts|hash|help|history|if|in|jobs|let|local|logout|popd|printf|pushd|read|readonly|return|select|set|shift|shopt|source|suspend|test|then|times|trap|true|type|typeset|umask|unalias|unset|until|wait|while|echo)/,
    ['',2]
 ],
 [
    /^(alias|bg|bind|break|builtin|caller|case|command|compgen|complete|continue|declare|dirs|disown|do|done|elif|else|enable|esac|eval|exec|exit|export|false|fc|fg|fi|for|getopts|hash|help|history|if|in|jobs|let|local|logout|popd|printf|pushd|read|readonly|return|select|set|shift|shopt|source|suspend|test|then|times|trap|true|type|typeset|umask|unalias|unset|until|wait|while|echo)\b/,
    ['<b><span style=\'color:ffef3f\'>',3],
    ['</span></b>',0]
 ],
 [
    /^(\[|\]|\||\|\||&&)/,
    ['<span style=\'color:ffef3f\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(\$\(\(.*?\)\))/,
    ['<span style=\'color:444fff\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^[\w_]\d+/,
    ['',2]
 ],
 [
    /^-?\d+/,
    ['<span style=\'color:ff4fbf\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(<<("|'|)([a-z0-9_]+)\2)((?:.|\n)*?)()(\n\3)/i,
    ['',0],
    ['<span style=\'color:ffff54\'>',3],
    ['</span>',0],
    ['',0],
    ['<span style=\'color:ff54ff\'><b>',3],
    ['</b></span>',0],
    ['<span style=\'color:ffff54\'>',3],
    ['</span>',0]
 ],
 [
    /^(`)()(.*?)()(`)/,
    ['',0],
    ['<span style=\'color:ff4f3f\'>',3],
    ['',0],
    ['<b>',3],
    ['</b>',0],
    ['',3],
    ['</span>',0]
 ]
];
