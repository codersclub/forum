var IE5 = (navigator.userAgent.indexOf('MSIE')!=-1) && (navigator.userAgent.indexOf('Opera')==-1)
var Opera7 = (navigator.userAgent.indexOf('Opera')!=-1) && (navigator.userAgent.charAt(navigator.userAgent.indexOf('Opera')+6)>=7)

function ins(name)
 {
   if (document.REPLIER) 
    {
      var input=document.REPLIER.Post;

      if (input.value != '') { input.value = input.value + '\n' };

      input.value=input.value + '[b]' + name + '[/b], ';
    } 
}

var txt='' 

function get_selection(qname, qdate) 
 { 
  txt='' 

  if (document.getSelection) { txt = document.getSelection(); } else if (document.selection) { txt = document.selection.createRange().text; } 

  txt='[quote][B]'+qname+'[/B], '+qdate+'\n'+txt+'[/quote]\n';
}


function Insert()
 { 
   var input = document.REPLIER.Post;

   if (input.value != '') { input.value = input.value + '\n' };

   input.value = input.value + txt;
 }