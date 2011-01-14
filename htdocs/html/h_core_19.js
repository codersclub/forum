var zamForBr="\n";
/*Безопаная(без	потери формата)	вставка	текста в тег PRE*/
function pasteInPre(Ob, newText)
{
    if(window.opera && Ob.innerHTML)
      Ob.innerHTML=newText;
    else
    if(Ob.outerHTML)//у всех осликов есть outerHTML
    //лечим ослика добавляя <PRE>
        Ob.outerHTML=Ob.outerHTML.substr(0,Ob.outerHTML.indexOf('>')+1)+newText+"<\/pre>";
    else
    if(document.namespaceURI)//konqueror
       Ob.innerHTML=newText.replace(reBR,'<br>');
    else//а у мозиллы   этого бага нету
        Ob.innerHTML=newText;
}
//#####################################
var	Ac={' ':0,'none':1,'value':2,'tag':3,'count':4}

//#####################################
var	H_Esc={'<':'&lt;','>':'&gt;','&':'&amp;'}
var	unH_Esc={'&lt;':'<','&gt;':'>','&amp;':'&','&quot;':'"','&nbsp;':' '}
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
			  function()/*tag*/{
				  var end_reached = false;
				  R+=reg[j][0]+
				  HTMLescape(m[j-1])
				  	.replace(/\n$/g, function(a){end_reached=true;
				  		return (reg[j+1]?reg[j+1][0]:'')+'</li><li>'; })
				  	.replace(/\n/g, (reg[j+1]?reg[j+1][0]:'')+'</li><li>' +reg[j][0]);
				  if (!end_reached) {
					  R+=(reg[j+1]?reg[j+1][0]:'')
				  }
			  },P];
/************/
	 var RL=Rul.length
	 if(!RL) m = 0; // patch from volvo877 for no highlight rules
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
				break;
			}//if
		}//for
		if(!m)
		 {	
			var C=H_Esc[T.charAt(0)]
			R+=C?C:T.charAt(0)
			T=T.substring(1)
			
		 }
	 }//while

 if(is_B)
	R=R.replace(/[\x01\x02]/g,function(a){return a.charCodeAt(0)==1?'<span style=color:red>':'</span>';})
 R="<ol type=\"1\"><li>"+R+"</li></ol>";
 R=R.replace(/\n/g, '</li><li>').replace(/<li>\s*?<\/li>/g, '<li>&nbsp;</li>').replace(/<li> /g, '<li>&nbsp;');
 R=R.replace(/  /g, ' &nbsp;');
 return	R;
}//parseCode

//#####################################
var is_B;
function getInnerText(o)
{
is_B=false;
return o.innerHTML.replace(/<(?:(\/)|([sS]))?[^>]*>|([\r\n])|(&\w+;)|(\xA0)/g,function(a,sz,so,c,b,nb){return b?unH_Esc[b]:c?'':nb?"\x20":(so?( is_B=true, "\x01"):(sz?"\x02":zamForBr))})}
//##################################### 
function parseOne(O,L)
{
 var RUL=window['h_'+L];
 O=document.getElementById(O);
 if(RUL)pasteInPre(O,parseCode(getInnerText(O),RUL))

}