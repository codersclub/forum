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
    /^(asm|new|delete|this|return|goto|if|else|case|default|switch|break|continue|while|do|for|try|catch|throw|sizeof|true|false|namespace|using|dynamic_cast|static_cast|reinterpret_cast|public|protected|private|class|virtual)\b/,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^(const|extern|auto|register|static|unsigned|signed|volatile|char|double|float|int|long|short|void|typedef|struct|union|enum|bool)\b/,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^(__cdecl|__fastcall|__property|__published)\b/,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^[a-z_][0-9a-z_]+/i,
    ['',2]
 ],
 [
    /^(#include|#pragma|#if|#endif|#define|#ifdef|#else|#elif|#ifndef)(.*?\n)/,
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
