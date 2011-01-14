window['h_perl']=[
 [
    /^#.*?(?:\n|$)/,
    ['<span style=\'color:669900\'>',3],
    ['</span>',0]
 ],
 [
    /^(?:<<)?(?:".*?[^\\]"|'.*?[^\\]'|""|'')/,
    ['<span style=\'color:CC0000\'>',3],
    ['</span>',0]
 ],
 [
    /^\&\$*(?:[a-zA-Z0-9_']|::)+/,
    ['<span style=\'color:FF0033\'>',3],
    ['</span>',0]
 ],
 [
    /^[\@\%\*\$]+(?:[\w']|::)+/,
    ['<span style=\'color:0000CC\'>',3],
    ['</span>',0]
 ],
 [
    /^(lt|gt|le|ge|cmp|or|and|xor|my|our|use|local|sub|do|package|require|foreach|while|if|else|elsif|until|unless|for|next|last|redo)\b/,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^\b(exists|delete|undef|defined|warn|die|exit|print|shift|pop|push|time|localtime|sort|keys|rand|index|substr|bless|tie|open|close)\b/,
    ['<span style=\'color:FF0033\'>',3],
    ['</span>',0]
 ],
 [
    /^\b(BEGIN|END|DESTROY)\b/,
    ['<span style=\'color:FF0033\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^[a-z_][0-9a-z_]+/i,
    ['',2]
 ],
 [
    /^(?:`.*?[^\\]`|``)/,
    ['<span style=\'color:9900FF\'>',3],
    ['</span>',0]
 ]
];
