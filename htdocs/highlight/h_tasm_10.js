window['h_tasm']=[
 [
    /^(include|includelib)([^a-z0-9_@\.&\$%\?])(.*?)(?=;|\\|\n)/i,
    ['',0],
    ['<span style=\'color:0000C0\'><b>',3],
    ['</b></span><span style=\'color:0090A0\'>',2],
    ['',3],
    ['</span>',0]
 ],
 [
    /^(echo|%out|name|title|procdesc)([^a-z0-9_@\.&\$%\?])(.*?)(?=;|\\|\n)/i,
    ['',0],
    ['<b>',3],
    ['</b><span style=\'color:0090A0\'>',2],
    ['',3],
    ['</span>',0]
 ],
 [
    /^(\.code|\.const|\.data|\.data\?|\.fardata|\.fardata\?|\.model|\.stack|\.startup|codeseg|const|dataseg|end|fardata|ideal|masm|model|proc|segment|stack|startupcode|udataseg|ufardata)(?=[^a-z0-9_@\.&\$%\?])/i,
    ['',0],
    ['<span style=\'color:0000C0\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(%bin|%conds|%cref|%crefall|%crefref|%crefuref|%ctls|%depth|%incl|%linum|%linum|%list|%macs|%newpage|%noconds|%nocref|%noctls|%noincl|%nolist|%nomacs|%nosyms|%notoc|%notrunc|%pagesize|%pcnt|%poplctl|%pushlctl|%subttl|%syms|%tabsize|%text|%title|%toc|%trunc|\.186|\.286|\.286c|\.286p|\.287|\.386|\.386c|\.386p|\.387|\.486|\.486c|\.486p|\.487|\.586|\.586c|\.586p|\.587|\.686|\.686c|\.686p|\.687|\.8086|\.8087|\.alpha|\.cref|\.dosseg|\.lall|\.lfcond|\.list|\.listall|\.listif|\.listmacro|\.listmacroall|\.mmx|\.nocref|\.nolist|\.nolistif|\.nolistmacro|\.nommx|\.radix|\.sall|\.seq|\.sfcond|\.tfcond|\.xall|\.xcref|\.xlist|\?debug|alias|align|arg|assume|catstr|comm|comment|db|dd|df|display|dosseg|dp|dq|dt|dw|emul|endm|endp|ends|enum|equ|even|evendata|exitm|export|extern|externdef|extrn|for|forc|goto|group|instr|irp|irpc|jumps|label|largestack|local|locals|macro|masm51|method|multerrs|noemul|nojumps|nolocals|nomasm51|nomulterrs|nosmart|nowarn|option|org|p186|p286|p286n|p287|p386|p386n|p387|p486|p486n|p487|p586|p586n|p587|p686|p686n|p687|p8086|p8087|pmmx|pno87|pnommx|popstate|proctype|proto|public|publicdll|purge|pushstate|quirks|radix|record|repeat|rept|returns|sizestr|smallstack|smart|stackalign|stackunalign|struc|struct|substr|subtitle|subttl|table|tblinit|tblinst|tblptr|textequ|title|typedef|union|uses|version|warn|while)(?=[^a-z0-9_@\.&\$%\?])/i,
    ['',0],
    ['<b>',3],
    ['</b>',0]
 ],
 [
    /^(\.err|\.err1|\.err2|\.errb|\.errdef|\.errdif|\.errdifi|\.erre|\.erridn|\.erridni|\.errnb|\.errndef|\.errnz|else|elseif|elseif1|elseif2|elseifb|elseifdef|elseifdif|elseifdifi|elseife|elseifidn|elseifidni|elseifnb|elseifndef|endif|err|errif|errif1|errif2|errifb|errifdef|errifdif|errifdifi|errife|errifidn|errifidni|errifnb|errifndef|if|if1|if2|ifb|ifdef|ifdif|ifdifi|ife|ifidn|ifidni|ifnb|ifndef)(?=[^a-z0-9_@\.&\$%\?])/i,
    ['',0],
    ['<span style=\'color:00A800\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(\.type|abs|at|basic|bss|byte|c|carry\?|code|codeptr|common|compact|cpp|data|dataptr|dgroup|dos|dup|dword|eq|execonly|execread|false|far|far16|far32|farstack|far_bss|far_data|flat|fortran|fword|ge|getfield|global|gt|high|huge|large|le|length|low|lt|mask|medium|memory|mempage|mod|ne|near|near16|near32|nearstack|nolanguage|normal|nothing|oddfar|oddnear|offset|os2|os_dos|os_nt|os_os2|overflow\?|page|para|parity\?|pascal|private|prolog|ptr|pword|qword|readonly|readwrite|real10|real4|real8|req|rest|sbyte|sdword|seg|setfield|short|sign\?|size|small|small|stdcall|sword|symtype|syscall|start|tbyte|tchuge|this|tiny|tpascal|true|type|uninit|unknown|use16|use32|vararg|virtual|width|windows|word|zero\?|_bss|_data|_text)(?=[^a-z0-9_@\.&\$%\?])/i,
    ['',0],
    ['<i>',3],
    ['</i>',0]
 ],
 [
    /^(\$|\?|\?\?date|\?\?filename|\?\?time|\?\?version|@32Bit|@@32Bit|@@Interface|@@Object|@B|@CodeSize|@Cpu|@DataSize|@F|@FileName|@Interface|@Model|@Object|@Startup|@WordSize|@code|@curseg|@data|@fardata|@fardata\?|@stack)(?=[^a-zA-Z0-9_@\.&\$%\?])/,
    ['',0],
    ['<i>',3],
    ['</i>',0]
 ],
 [
    /^(\.break|\.continue|\.else|\.elseif|\.endif|\.endw|\.exit|\.if|\.repeat|\.until|\.untilcxz|\.while|call|exitcode|iret|iretd|iretdf|iretf|iretw|ja|jae|jb|jbe|jc|jcxz|je|jecxz|jg|jge|jl|jle|jmp|jna|jnae|jnb|jnbe|jnc|jne|jne|jng|jnge|jnl|jnle|jnp|jns|jnz|jo|jp|jpe|jpo|js|jz|loop|loopd|loopde|loopdne|loopdnz|loopdz|loope|looped|loopew|loopne|loopned|loopnew|loopnz|loopnzd|loopnzw|loopw|loopwe|loopwne|loopwnz|loopwz|loopz|loopzd|loopzw|ret|retcode|retf|retn)(?=[^a-z0-9_@\.&\$%\?])/i,
    ['',0],
    ['<span style=\'color:888888\'><b>',3],
    ['</b></span>',0]
 ],
 [
    /^(ah|al|ax|bh|bl|bp|bx|ch|cl|cr0|cr2|cr3|cr4|cs|cs|cx|dh|di|dl|dr0|dr1|dr2|dr3|dr5|dr6|dr7|ds|ds|dx|eax|ebp|ebx|ecx|edi|edx|es|es|esi|esp|fs|fs|gs|gs|mm0|mm1|mm2|mm3|mm4|mm5|mm6|mm7|si|sp|ss|ss|st|tr3|tr4|tr5|tr6|tr7)(?=[^a-z0-9_@\.&\$%\?])/i,
    ['',0],
    ['<span style=\'color:009000\'>',3],
    ['</span>',0]
 ],
 [
    /^[a-z_@\.&\$%\?][a-z0-9_@\.&\$%\?]*/i,
    ['',2]
 ],
 [
    /^;.*?\n/,
    ['<span style=\'color:A0A0A0\'>',3],
    ['</span>',0]
 ],
 [
    /^(\\)(.*?\n)/,
    ['',0],
    ['',2],
    ['<span style=\'color:A0A0A0\'>',3],
    ['</span>',0]
 ],
 [
    /^'.*?('|\n)/,
    ['<span style=\'color:0090A0\'>',3],
    ['</span>',0]
 ],
 [
    /^".*?("|\n)/,
    ['<span style=\'color:0090A0\'>',3],
    ['</span>',0]
 ]
];
