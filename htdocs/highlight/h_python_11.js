window['h_python']=[
 [
    /^#!\/usr\/[\w\d_\/]+?python\b/,
    ['<b><span style=\'color:green\'>',3],
    ['</span></b>',0]
 ],
 [
    /^#.*?(?:\n|$)/,
    ['<i><span style=\'color:75a7a2\'>',3],
    ['</span></i>',0]
 ],
 [
    /^"""(?:.|\n)*?"""/,
    ['<i><span style=\'color:75a7a2\'>',3],
    ['</span></i>',0]
 ],
 [
    /^("|')(\\.|.|\n)*?(\1)/,
    ['<span style=\'color:4070a0\'>',3],
    ['</span>',0]
 ],
 [
    /^[\w\d_](import|class|def|pass|if|else|elif|return|print|exit|from|self|True|False|None|in|and|for|labmda|for|try|except)/,
    ['',2]
 ],
 [
    /^(self|True|False|None)\b/,
    ['',0],
    ['<span style=\'color:007020\'>',3],
    ['</span>',0]
 ],
 [
    /^(import|from|class|def|pass|if|else|elif|return|print|exit|in|for|or|and|labmda|as|try|except)\b/,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ]
];
