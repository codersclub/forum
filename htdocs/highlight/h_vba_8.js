window['h_vba']=[
 [
    /^'.*?\n/,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^On\s+Error\s+GoTo\s+0/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Boolean|Byte|CByte|Currency|Date|Double|Integer|Long|Object|Single|String|Variant|Any|Empty|Null|Error|True|False|Nothing)\b/i,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Dim|ReDim|As|Enum|New|Set|LSet|Let|Type|Const|Option|Explicit|WithEvents)\b/i,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(If|IIf|Then|Else|ElseIf|EndIf|End|Select|Case|For|To|Step|Next|Each|In|Do|While|Loop|Until|Exit|Wend|GoTo|With|Resume|On)\b/i,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Sub|Function|Property|Private|Public|Static|Global|Call|Optional|ByVal|ByRef|Declare|Alias|Local|Lib)\b/i,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Open|Close|Print|Get|Input|Line|Put|Write|Append|Output|Binary)\b/i,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Circle|Stop|Scale|Preserve|Erase|Debug|Cling|Access|Assert)\b/i,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Mod|Xor|Not|UBound|LBound)\b/i,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^[a-z_][0-9a-z_]+/i,
    ['',2]
 ],
 [
    /^\".*?(\n|\")/,
    ['<span style=\'color:gray\'>',3],
    ['</span>',0]
 ]
];
