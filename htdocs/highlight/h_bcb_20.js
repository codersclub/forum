window['h_bcb']=[
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
    /^(__finally|asm|break|case|catch|continue|default|delete|do|dynamic_cast|else|false|for|goto|if|namespace|new|private|protected|public|reinterpret_cast|return|sizeof|static_cast|switch|this|throw|true|try|using|virtual|while)/,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^(auto|bool|char|class|const|double|enum|extern|float|int|long|register|short|signed|static|struct|template|typedef|union|unsigned|void|volatile)\b/,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^(__cdecl|__closure|__fastcall|__property|__published|__stdcall)\b/,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^[a-z_][0-9a-z_]+/i,
    ['',2]
 ],
 [
    /^(#include|#pragma|#if|#endif|#define|#ifdef|#else|#elif|#ifndef|#undef)(.*?\n)/,
    ['',0],
    ['<span style=\'color:green\'>',3],
    ['',3],
    ['</span>',0]
 ],
 [
    /^\"(\\.|.)*?(\n|\")/,
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
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^[0-9]+\.[0-9]*(e[-+][0-9]+)?[fl]?/i,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^0x[0-9a-f]+/i,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ],
 [
    /^[0-9]+u?(ll|l|i64|i32|i16|i8)?/i,
    ['<span style=\'color:darkblue\'>',3],
    ['</span>',0]
 ]
];
