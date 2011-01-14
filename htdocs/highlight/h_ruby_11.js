window['h_ruby']=[
 [
    /^#!\/usr\/[\w\d_\/]+?ruby\d+\b/,
    ['<b><span style=\'color:green\'>',3],
    ['</span></b>',0]
 ],
 [
    /^#.*?(?:\n|$)/,
    ['<i><span style=\'color:75a7a2\'>',3],
    ['</span></i>',0]
 ],
 [
    /^("|')(\\.|.|\n)*?(\1)/,
    ['<span style=\'color:4070a0\'>',3],
    ['</span>',0]
 ],
 [
    /^[\w\d_](new|true|false|nil|for|in|include|raise|not|redo|retry|while|yield|then|until|require|if|elsif|else|end|next|rescue|class|def|undef|return|unless|when|case|or|and|do|ensure|break|begin|BEGIN|END|defined?|private_class_method|private|protected|public_class_method|public|attr_(?:reader|writer|accessor))/,
    ['',2]
 ],
 [
    /^(new|true|false|nil|self)\b/,
    ['',0],
    ['<span style=\'color:007020\'>',3],
    ['</span>',0]
 ],
 [
    /^(for|in|include|not|redo|retry|while|raise|yield|then|until|require|if|elsif|else|end|next|rescue|class|def|undef|return|unless|when|case|or|and|do|ensure|break|begin|BEGIN|END|defined?)\b/,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^<<-("|'|)([a-z0-9_]+)\1(.|\n)*?\2/i,
    ['<span style=\'color:4070a0\'>',3],
    ['</span>',0]
 ],
 [
    /^(private_class_method|private|protected|public_class_method|public)\b/,
    ['',0],
    ['<b><span style=\'color:blue\'>',3],
    ['</span></b>',0]
 ],
 [
    /^attr_(?:reader|writer|accessor)\b/,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^(\$|@)[\w_@][\w\d_]*/,
    ['<span style=\'color:C80000\'>',3],
    ['</span>',0]
 ],
 [
    /^(=begin)()((?:.|\n)*?)()(=end)/,
    ['',0],
    ['<span style=\'color:3F7F5F\'><b>',3],
    ['</b></span>',0],
    ['<span style=\'color:3F7F5F\'>',3],
    ['</span>',0],
    ['<span style=\'color:3F7F5F\'><b>',3],
    ['</b></span>',0]
 ]
];
