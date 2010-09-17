window['h_cpp']=[
 [
    /^\/\/.*?\n/,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^\/\*(.|\n)*?\*\//,
    ['<span style=\'color:green\'>',3],
    ['</span>',0]
 ],
 [
    /^(__declspec)([^a-zA-Z0-9])(dllexport|dllimport|uuid|selectany|align)/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',2],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(new|delete|this|return|goto|if|else|case|default|switch|break|continue|while|do|for|try|catch|throw|sizeof|true|false|namespace|using|dynamic_cast|static_cast|reinterpret_cast|public|protected|private|class|asm|_asm|__asm|template|virtual|__try|__catch|__except|__finaly|operator)/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(const|extern|auto|register|static|unsigned|signed|volatile|char|double|float|int|long|short|void|typedef|struct|union|enum|bool|__int8|__int16|__int32|__int64)\b/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(__cdecl|__stdcall|__thiscall|__declspec|mutable|explicit)\b/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(__uuidof)\b/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(std|stdext|vector|list|set|map|deque|queue|stack|string|hash_map|hash_set|ifstream|ofstream)\b/,
    ['',0],
    ['<span style=\'color:006464\'>',3],
    ['</span>',0]
 ],
 [
    /^[a-z_][0-9a-z_]+/i,
    ['',2]
 ],
 [
    /^(#if)()([^0-1a-zA-Z]{1,2}|.*?)()(defined)/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0],
    ['',3],
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(#include\s*)(<)([^<>]*?)(>)/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',4],
    ['<span style=\'color:darkblue\'>&lt;',3],
    ['&gt;</span>',4]
 ],
 [
    /^(#pragma\s\s*?comment)([^a-zA-Z0-9]+)(lib)/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',2],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(#pragma\s\s*?pack)([^a-zA-Z0-9]+)(push|pop)/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',2],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^#pragma\s\s*?(function|once|comment)/,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^(#include|#pragma|#if|#endif|#define|#ifdef|#else|#ifndef|#error|#undef)\b/,
    ['',0],
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^L?\"(\\.|.)*?(\n|\")/,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^'(\\.|.)*?(\n|')/,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^\.[0-9]+(e[+-][0-9]+)?[fl]?/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^[0-9]+\.[0-9]*(e[-+][0-9]+)?[fl]?/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^0x[0-9a-f]+/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ],
 [
    /^[0-9]+u?(ll|l|i64|i32|i16|i8)?/i,
    ['<span style=\'color:blue\'>',3],
    ['</span>',0]
 ]
];
