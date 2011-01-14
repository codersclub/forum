window['h_vbnet']=[
 [
    /^('|Rem\b)(.*?_\n)*.*?(?:\n|$)/i,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^On Error GoTo 0/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^On Error/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^Option (Compare Binary|Compare Text|Explicit On|Explicit Off|Strict On|Strict Off)/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^Declare (Unicode|Ansi|Auto)\b/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Boolean|Byte|Char|Date|Decimal|Double|Integer|Long|Object|SByte|Short|Single|String|UInteger|ULong|UShort)\b/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(CBool|CByte|CChar|CDate|CDbl|CDec|CInt|CLng|CObj|CSByte|CShort|CSng|CStr|CType|CUInt|CULng|CUShort|True|False|Nothing)\b/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Static|Dim|As|ReDim|Preserve|ByVal|ByRef|New|Set|Get|Const|AddressOf|WithEvents|String|Byte|Global|Public|Protected|Friend|Friend|Private)\b/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(AddHandler|Call|Class|Continue|Declare|Delegate|Do|While|Until|Loop|End|Enum|Erase|Error|(Custom Event|Event)|Exit|For|Each|To|Step|Next|Function|GoTo|If|Then|ElseIf|Else|Implements|Imports|Inherits|Interface|Module|Namespace|Operator|Property|RaiseEvent|RemoveHandler|Resume|Return|Select|Case|Stop|Structure|Sub|SyncLock|Throw|Try|Catch|Finally|Using|While|With)\b/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(And|AndAlso|GetType|Is|IsNot|Like|Mod|Not|Or|OrElse|Is|Xor)\b/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(Alias|Default|DirectCast|Handles|In|Lib|Me|My|MustInherit|MustOverride|MyBase|MyClass|Narrowing|NotInheritable|NotOverridable|Of|On|Option|Optional|Overloads|Overridable|Overrides|ParamArray|Partial|ReadOnly|Shadows|Shared|TryCast|When|Widening|WriteOnly)\b/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^[a-z_][0-9a-z_]+/i,
    ['',2]
 ],
 [
    /^\".*?\"/,
    ['<span style=\'color:darkred\'>',3],
    ['</span>',0]
 ],
 [
    /^(\n|\s\|\>)\<([\/0-9A-Za-z]+)(.*?_\n|\>)*.*?\>/i,
    ['<span style=\'color:gray\'>',3],
    ['</span>',0]
 ]
];
