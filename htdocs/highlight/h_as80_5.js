window['h_as80']=[
 [
    /^(\.ascii|\.asciz|\.blkb|\.byte|\.end|\.entry|\.word|db|ds|dw|end|equ|org)(?=[^a-z0-9_@\.&\$%\?])/i,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^(aci|adc|add|adi|ana|ani|cma|cmc|cmp|cpi|daa|dad|dcr|dcx|di|ei|hlt|in|inr|inx|lda|ldax|lhld|lxi|mov|mvi|nop|ora|ori|out|pop|push|ral|rar|rlc|rrc|sbb|sbi|shld|sphl|sta|stax|stc|sub|sui|xchg|xra|xri|xthl)(?=[^a-z0-9_@\.&\$%\?])/i,
    ['',0],
    ['<span style=\'color:0000A0\'>',3],
    ['</span>',0]
 ],
 [
    /^(call|cc|cm|cnc|cnz|cp|cpe|cpo|cz|jc|jm|jmp|jnc|jnz|jp|jpe|jpo|jz|pchl|rc|ret|rm|rnc|rnz|rp|rpe|rpo|rst|rz)(?=[^a-z0-9_@\.&\$%\?])/i,
    ['',0],
    ['<span style=\'color:0000A0\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(a|b|bc|c|d|de|e|h|hl|l|m|psw|sp)(?=[^a-z0-9_@\.&\$%\?])/i,
    ['',0],
    ['<span style=\'color:009000\'><i>',3],
    ['</i></span>',0]
 ],
 [
    /^[a-z0-9_@\.&\$%\?]+/i,
    ['',2]
 ],
 [
    /^;.*?\n/,
    ['<span style=\'color:A0A0A0\'>',3],
    ['</span>',0]
 ],
 [
    /^".*?("|\n)/,
    ['<span style=\'color:0090A0\'>',3],
    ['</span>',0]
 ]
];
