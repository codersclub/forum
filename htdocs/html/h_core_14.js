/*Безопаная(без	потери формата)	вставка	текста в тег PRE*/
function pasteInPre(Ob,	newText)
{
	if(window.opera	&& Ob.innerHTML)
	  Ob.innerHTML=newText;
	else
	if(Ob.outerHTML)//у	всех осликов есть outerHTML
	//лечим	ослика добавляя	<PRE>
		Ob.outerHTML=Ob.outerHTML.substr(0,Ob.outerHTML.indexOf('>')+1)+newText+"<\/pre>";
	 else//а у мозиллы	этого бага нету
		Ob.innerHTML=newText;

}
//#####################################
var	Ac={' ':0,'none':1,'value':2,'tag':3,'count':4}
var	H_Esc={'<':'&lt;','>':'&gt;','&':'&amp;'}
var	unH_Esc={'&lt;':'<','&gt;':'>','&amp;':'&','&quot;':'"'}
//#####################################
function HTMLescape(s)
{
 return	s.replace(/[<&>]/g,function(k){return H_Esc[k]})
}
//#####################################
function parseCode(T,Rul)
{var m=1,R='';
/*************/
function P(){}
var	AcResp=[P,function()/*none*/{R+=reg[j][0]+(reg[j+1]?reg[j+1][0]:'')},
			  function()/*value*/{R+=HTMLescape(m[j-1])},
			  function()/*tag*/{R+=reg[j][0]+HTMLescape(m[j-1])+(reg[j+1]?reg[j+1][0]:'')},P];
/************/

if(isB=T.indexOf('{b}')!=-1	|| T.indexOf('{B}')!=-1)
	T=T.replace(/\{(\/)?[bB]\}/g,function(a,b){return b?'\x02':'\x01'});


 var RL=Rul.length
 while(T)
 {	
	for(var	i=0;i<RL;i++)
	{
		var	reg=Rul[i];
		if(m=reg[0].exec(T))
		{
			for(var	j=1;j<reg.length;j++)
			   AcResp[reg[j][1]]();
			T=T.substring(m[0].length);
			break
		}//if
	}//for
	if(!m)
	 {	
		R+=H_Esc[T.charAt(0)]
		T=T.substring(1);
	 }
 }//while
 if(isB)R=R.replace(/[\x01\x02]/g,function(a){return a.charCodeAt(0)==1?'<span style=color:red>':'</span>';})
 return	"<span>"+R+"</span>"   //Mozilla bugfix
}//parseCode

//#####################################
var	getInnerText=new Function('o',
window.opera?
 "var S='',t,m=o.childNodes;"+
 "var d=m.length;			"+
 "for(var i=0;i<d;i++)		"+
 "{							"+
 "	 t=m[i].nodeValue;		"+
 "	 S+=t?t:'\\n'			"+
 "}							"+
 "return S					"
:
"return o.innerHTML.replace(/<[Bb][Rr]>|(&\\w+;)/g,function(a,b){return b?unH_Esc[b]:'\\n'})"
);
 
function parseOne(O,L)
{
 O=document.getElementById(O);
// var v=new Date()
 pasteInPre(O,parseCode(getInnerText(O),window['h_'+L]))//O.firstChild.nodeValue
 //alert(new Date -	v)

}