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
    /^On Error/i,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^Option Base (0|1)/i,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^Option Explicit/i,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^Option Compare (Text|Binary)/i,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^As (Any|Boolean|Byte|Currency|Date|Double|Integer|Long|Object|Single|String|Variant)/i,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^(CBool|CByte|CCur|CDate|CDbl|CDec|CInt|CLng|CSng|CVar|CStr|Empty|Is|Null|True|False|Nothing)/i,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Dim|As|ReDim|Enum|New|Set|LSet|Let|Type|Const|AddressOf|WithEvents)/i,
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
    /^(Mod|Xor|Not|And|Or|UBound|LBound)\b/i,
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
