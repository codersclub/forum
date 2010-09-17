window['h_res']=[
 [
    /^(#define|#elif|#else|#endif|#if|#ifdef|#ifndef|#include|#undef)\b/i,
    ['',0],
    ['<span style=\'color:00A800\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(accelerators|bitmap|cursor|dialogex|dialog|icon|menu|menuex|messagetable|popup|rcdata|stringtable|versioninfo)\b/i,
    ['',0],
    ['<span style=\'color:0000C0\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(auto3state|autocheckbox|autoradiobutton|checkbox|combobox|control|ctext|defpushbutton|edittext|groupbox|listbox|ltext|pushbox|pushbutton|radiobutton|rtext|scrollbar|state3)\b/i,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^(block|caption|characteristics|class|exstyle|font|language|menuitem|style|version)\b/i,
    ['',0],
    ['<span style=\'color:0000C0\'>',3],
    ['</span>',0]
 ],
 [
    /^(alt|ascii|checked|discardable|false|fileflags|fileflagsmask|fileos|filesubtype|filetype|fileversion|fixed|grayed|help|impure|inactive|loadoncall|menubarbreak|menubreak|moveable|noinvert|null|preload|productversion|pure|separator|shift|true|value|virtkey)\b/i,
    ['',0],
    ['<i>',3],
    ['</i>',0]
 ],
 [
    /^(begin|end|\{|\})\b/i,
    ['',0],
    ['<span style=\'color:909090\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^[a-z_][a-z0-9_]*/i,
    ['',2]
 ],
 [
    /^\/\/.*?\n/,
    ['<span style=\'color:A0A0A0\'>',3],
    ['</span>',0]
 ],
 [
    /^\/\*(.|\n)*?\*\//,
    ['<span style=\'color:A0A0A0\'>',3],
    ['</span>',0]
 ],
 [
    /^'.*?('|\n)/,
    ['<span style=\'color:00A0A0\'>',3],
    ['</span>',0]
 ],
 [
    /^".*?("|\n)/,
    ['<span style=\'color:0090A0\'>',3],
    ['</span>',0]
 ]
];
