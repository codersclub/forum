window['h_diff']=[
 [
    /(^Index: .*)|(^\+\+\+ .*)|(^--- .*)|(^diff.*)|(^==== .*)|(^\*\*\* .*)/i,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^@@.*?@@/,
    ['<span style=\'color:brown\'>',3],
    ['</span>',0]
 ],
 [
    /^-.*/,
    ['<b><span style=\'color:purple\'>',3],
    ['</span></b>',0]
 ],
 [
    /^\+.*/,
    ['<b><span style=\'color:navy\'>',3],
    ['</span></b>',0]
 ]
];
