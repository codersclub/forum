window['h_vba']=[
 [
    /^('|Rem)(.*?_\n)*.*?\n/,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^On\s+Error\s+GoTo\s+0/i,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^On Error/,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^Option Base (0|1)/,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^Option Explicit/,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^Option Compare (Text|Binary)/,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Boolean|Byte|CByte|Currency|Date|Double|Integer|Long|Object|Single|String|Variant|Any|Empty|Null|True|False|Nothing)\b/i,
    ['',0],
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Dim|ReDim|As|Enum|New|Set|LSet|Let|Type|Const|AddressOf|WithEvents)\b/i,
    ['',0],
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^(If|Then|Else|ElseIf|EndIf|End|Select|Case|For|To|Step|Next|Each|In|Do|While|Loop|Until|Exit|Wend|GoTo|With|Resume|On)\b/i,
    ['',0],
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Sub|Function|Property|Private|Public|Static|Global|Call|Optional|ByVal|ByRef|Declare|Alias|Local|Lib)\b/i,
    ['',0],
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Open|Close|Print|Get|Input|Line|Put|Lock|Read|Write|Append|Output|Binary)\b/i,
    ['',0],
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Circle|Stop|Scale|Preserve|Erase|Debug|Cling|Access|Assert)\b/i,
    ['',0],
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Mod|Xor|Not|UBound|LBound)\b/i,
    ['',0],
    ['<span style=\'color:darkblue\'>',3],
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
