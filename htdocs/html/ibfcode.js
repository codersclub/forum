//--------------------------------------------
// Set up our simple tag open values
//--------------------------------------------
//
// Modified by Volker Puttrich to allow IE 4+
// on windows to use cursor position for inserting
// tags / smilies

var B_open = 0;
var I_open = 0;
var U_open = 0;
var S_open = 0;
var O_open = 0;
var C_open = 0;
var R_open = 0;
var L_open = 0;
var sub_open = 0;
var sup_open = 0;
var MOD_open = 0;
var EX_open = 0;
var MM_open = 0;
var GM_open = 0;
var doHTML_open = 0;
var USER_open = 0;
var SF_open = 0;
var ST_open = 0;
var SALL_open = 0;
var STALL_open = 0;
var QUOTE_open = 0;
var CODE_open = 0;
var SQL_open = 0;
var HTML_open = 0;
var Spoiler_open = 0;
var QUOTE_open = 0;
var td_open = 0;
var tr_open = 0;
var th_open = 0;
var table_open = 0;

var bbtags   = new Array();

// Determine browser type and stuff.
// Borrowed from http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html

var myAgent   = navigator.userAgent.toLowerCase();
var myVersion = parseInt(navigator.appVersion);

var is_ie   = ((myAgent.indexOf("msie") != -1)  && (myAgent.indexOf("opera") == -1));
var is_nav  = ((myAgent.indexOf('mozilla')!=-1) && (myAgent.indexOf('spoofer')==-1)
                && (myAgent.indexOf('compatible') == -1)
                && (myAgent.indexOf('webtv') ==-1)       && (myAgent.indexOf('hotjava')==-1))
                || (myAgent.indexOf('opera')!=-1)
                || (myAgent.indexOf('konqueror')!=-1);
var is_webkit = (myAgent.indexOf('webkit')!=-1)
                ;

var is_win   =  ((myAgent.indexOf("win")!=-1) || (myAgent.indexOf("16bit")!=-1));
var is_mac    = (myAgent.indexOf("mac")!=-1);

//--------------------------------------------
// Set the help bar status
//--------------------------------------------

// Set the number of tags open box

function cstat()
{
	var c = stacksize(bbtags);
	
	if ( (c < 1) || (c == null) ) {
		c = 0;
	}
	
	if ( ! bbtags[0] ) {
		c = 0;
	}
}

//--------------------------------------------
// Get stack size
//--------------------------------------------

function stacksize(thearray)
{
	for (i = 0 ; i < thearray.length; i++ ) {
		if ( (thearray[i] == "") || (thearray[i] == null) || (thearray == 'undefined') ) {
			return i;
		}
	}
	
	return thearray.length;
}

//--------------------------------------------
// Push stack
//--------------------------------------------

function pushstack(thearray, newval)
{
	arraysize = stacksize(thearray);
	thearray[arraysize] = newval;
}

//--------------------------------------------
// Pop stack
//--------------------------------------------

function popstack(thearray)
{
	arraysize = stacksize(thearray);
	theval = thearray[arraysize - 1];
	delete thearray[arraysize - 1];
	return theval;
}


//--------------------------------------------
// Close all tags
//--------------------------------------------

function closeall()
{
	if (bbtags[0]) {
		while (bbtags[0]) {
			tagRemove = popstack(bbtags)
			document.REPLIER.Post.value += "[/" + tagRemove + "]";
			
			// Change the button status
			// Ensure we're not looking for FONT, SIZE or COLOR as these
			// buttons don't exist, they are select lists instead.
			
			if ( (tagRemove != 'FONT') && (tagRemove != 'SIZE') && (tagRemove != 'COLOR') )
			{
				eval("document.REPLIER." + tagRemove + ".value = ' " + tagRemove + " '");
				eval(tagRemove + "_open = 0");
			}
		}
	}
	
	// Ensure we got them all
	bbtags = new Array();
	document.REPLIER.Post.focus();
}

//--------------------------------------------
// EMOTICONS
//--------------------------------------------

function emoticon(theSmilie)
{
	doInsert(" " + theSmilie + " ", "", false);
}

//--------------------------------------------
// ADD CODE
//--------------------------------------------

function add_code(NewCode)
{
    document.REPLIER.Post.value += NewCode;
    document.REPLIER.Post.focus();
}

//--------------------------------------------
// ALTER FONT
//--------------------------------------------

function alterfont(theval, thetag)
{
    if ( theval == 0 ) return;
    if ( theval == -1 ) return;
	
    if (doInsert("[" + thetag + "=" + theval + "]", "[/" + thetag + "]", true))
    if ( thetag != "CODE" ) pushstack(bbtags, thetag);
	
    document.REPLIER.ffont.selectedIndex  = 0;
    document.REPLIER.fsize.selectedIndex  = 0;
    document.REPLIER.fcolor.selectedIndex = 0;

    if ( document.REPLIER.syntax ) document.REPLIER.syntax.selectedIndex = 0;
    
    cstat();
	
}


//--------------------------------------------
// SIMPLE TAGS (such as B, I U, etc)
//--------------------------------------------

function simpletag(thetag)
{
	var tagOpen = eval(thetag + "_open");

//alert("simpletag started");
	if (tagOpen == 0)
	{
		if(doInsert("[" + thetag + "]", "[/" + thetag + "]", true))
		{
			eval(thetag + "_open = 1");
			// Change the button status
			eval("document.REPLIER." + thetag + ".value += '*'");
	
			pushstack(bbtags, thetag);
			cstat();
		}
	} else 
	{
		// Find the last occurance of the opened tag
		lastindex = 0;
		
		for (i = 0 ; i < bbtags.length; i++ )
		{
			if ( bbtags[i] == thetag )
			{
				lastindex = i;
			}
		}
		
		// Close all tags opened up to that tag was opened
		while (bbtags[lastindex])
		{
			tagRemove = popstack(bbtags);
			doInsert("[/" + tagRemove + "]", "", false)
			
			// Change the button status
			if ( (tagRemove != 'FONT') && (tagRemove != 'SIZE') && (tagRemove != 'COLOR') )
			{
				eval("document.REPLIER." + tagRemove + ".value = ' " + tagRemove + " '");
				eval(tagRemove + "_open = 0");
			}
		}
		
		cstat();
	}
}


function tag_list()
{
  if (last=document.getElementById('list_dialog')) document.body.removeChild(last);
  if (document.selection) var selection_range=document.selection.createRange();
  text = "<div class='jqcd_dialog'><div class='jqcd_content_layer'><div class='jqcd_content' style='padding-left: 10px; padding-right: 10px;'><form name=list_form onSubmit='return false'><br><select name='list_type' id='list_type' size=1 class='codebuttons' style='margin:0px 0px 4px'><option selected value=''>"+tag_list_marked+"<option value='=1'>"+tag_list_numbered+"<option value='=I'>"+tag_list_numbered_rome+"</select><br>"+list_prompt+"<br><input type='text' style='width:98%; margin:4px 0px 0px' name='list_field0' id='list_field0' value='' class='forminput'><br id='list_field0_br'></form></div></div></div>";
  var newDiv = document.createElement('div');
  newDiv.id='list_dialog';
  newDiv.innerHTML = text;
  document.body.appendChild(newDiv);
  $(function(){
    $('#list_dialog').jqcd({
      width: 420,
      height: 230,
      title_text: text_list,
      buttons_position: 'center',
      position: 'center' 
    });
   
    var is_inserting_field=false;
    
    $('#list_dialog').jqcd_open();
    
    function OKBtn()
    {
      if (selection_range) 
      {
        selection_range.select();
        document.forms.REPLIER.Post.focus();
      }
	var thelist = "";
      var base_name='list_field';
      var current_id=0;
      next='';
      while (current_node=document.getElementById(base_name+current_id)) 
      {
        if (current_id!=0) thelist+="[*]"+next+"\n";
        next=current_node.value;
	++current_id;
      }
      if (next!='') thelist+="[*]"+next+"\n";      
      if ( thelist != "" )
      {
      	doInsert( "[LIST"+document.getElementById('list_type').value+"]\n" + thelist + "[/LIST]\n", "", false);
      }
	
      $('#list_dialog').jqcd_close();
    }
    $('#list_dialog').jqcd_add_button('list_ok_button', 'OK',true,OKBtn); 
    $('#list_dialog').keypress(function(event)
	{
      if (event.which == 13) 
		{
        OKBtn(); 
	return false; 
		}
    });
    $('#list_dialog').jqcd_add_button('list_cancel_button', text_cancel, false,function() {$('#list_dialog').jqcd_close(); });

    function addField(number)
    {
        var newInput=document.createElement("input");
        newInput.type="text";
	var id="list_field"+number;
        newInput.id=id;
        newInput.name=id;
        newInput.value="";
        document.forms.list_form.appendChild(newInput);
        $('#'+id).keyup(function(){ checkForNewField(number); });
        $('#'+id).change(function(){ checkForNewField(number);});
        $('#'+id).addClass('forminput');
        $('#'+id).width('98%');
        $('#'+id).css("margin", "0px 0px");
	var newBr=document.createElement('br');
	newBr.id=id+'_br';
        document.forms.list_form.appendChild(newBr);
	}
	
    function checkForNewField(number_cur)
    {
      if (is_inserting_field) return 0;
      is_inserting_field=true;
      var current=document.getElementById("list_field"+number_cur);
      var number=number_cur+1;
      id = "list_field"+number;
      if (current.value && !document.getElementById(id))
      {
        addField(number);
      }
      checkForRemoveField(number_cur);
      is_inserting_field=false;
    }

    function checkForRemoveField(number_cur)
    {
     if ((current=document.getElementById("list_field"+number_cur)) && (current.value==''))
     { 
       var last_not_null=-1;
       number=0;
       while (current=document.getElementById("list_field"+number))
       {
         if (current.value) last_not_null=number;
	 number++;
       }
       number=last_not_null+2;
       while (current=document.getElementById("list_field"+number))
       {
         document.forms.list_form.removeChild(current);
	 if (current_br=document.getElementById("list_field"+number+'_br')) document.forms.list_form.removeChild(current_br);
	 number++;
       }
       if (number_cur>=last_not_null+2) $('#list_field'+(last_not_null+1)).focus();
     }
    }
    if (selection=getSelectedText(document.REPLIER.Post))
	{
      var splitted=selection.split("\n");
      for(var line=0; line<splitted.length; line++)
      {
        if (!document.getElementById("list_field"+line))
          addField(line);
        var current=document.getElementById("list_field"+line);
	current.value=splitted[line];
	}
      $('#list_field'+(line-1)).focus();
    } else  $('#list_field0').focus();
    $('#list_field0').keyup(function(){ checkForNewField(0); });   
    $('#list_field0').change(function(){ checkForNewField(0); });   
 });
}

function tag_table()
{
  doInsert("[table]\n[tr]\n [th][/th]\n [th][/th]\n[/tr]\n[tr]\n [td][/td]\n [td][/td]\n[/tr]\n[/table]", '', false);
}
  
  
function tag_url()
{
  if (last=document.getElementById('url_dialog')) document.body.removeChild(last);
  if (document.selection) var selection_range=document.selection.createRange();
  var selection=getSelectedText(document.REPLIER.Post);
  var url='http://';
  var name='';
  if (/^https?:\/\//i.test(selection)) 
  {
    var for_focus="url_url_name";
    url=selection;
  } else
  if (/^www\./i.test(selection))
  {
    var for_focus="url_url_name";
    url=selection;
  } else
  {
    var for_focus="url_url";
    name=selection;
  }
    
  text = "<div class='jqcd_dialog'><div class='jqcd_content_layer'><div class='jqcd_content' style='padding-left: 10px; padding-right: 10px'><br>"+text_enter_url+"<br><form name=url_form onSubmit='return false'><input type='url' style='width:98%; margin: 4px 0px' maxlength='255' name='url_url' id='url_url' value='"+url+"' class='forminput'><br>"+text_enter_url_name+"<br><input type='text' style='width:98%; margin:4px 0px' maxlength='255' name='url_url_name' id='url_url_name' value='"+name+"' class='forminput'></form></div></div></div>";
  var newDiv = document.createElement('div');
  newDiv.id='url_dialog';
  newDiv.innerHTML = text;
  document.body.appendChild(newDiv);
  $(function(){
    $('#url_dialog').jqcd({
      width: 420,
      height: 180,
      title_text: text_url,
      buttons_position: 'center',
      position: 'center' 
    });
    
    $('#url_dialog').jqcd_open();
    
    function OKBtn()
    {
      if (selection_range) 
    {
        selection_range.select();
        document.forms.REPLIER.Post.focus();
        } 
      url = document.forms.url_form.url_url.value;
      name = document.forms.url_form.url_url_name.value;
      if (url=="http://")  url=""; 
      if (name=='') { name = url }
      doInsert("[URL="+url+"]"+name+"[/URL]", "", false);
      $('#url_dialog').jqcd_close();
    }
    $('#url_dialog').jqcd_add_button('url_ok_button', 'OK',true,OKBtn); 
    $('#url_dialog').keypress(function(event){ if (event.which == 13) OKBtn(); });
    $('#url_dialog').jqcd_add_button('url_cancel_button', text_cancel, false,function(){$('#url_dialog').jqcd_close();});
    $('#'+for_focus).select();
    $('#'+for_focus).focus();
 });
}

function tag_image()
{
  if (last=document.getElementById('img_dialog')) document.body.removeChild(last);
  if (document.selection) var selection_range=document.selection.createRange();
  var selection=getSelectedText(document.REPLIER.Post);
  var url='http://';
  if (/^http:\/\//i.test(selection)) url=selection;    
  text = "<div class='jqcd_dialog'><div class='jqcd_content_layer'><div class='jqcd_content' style='padding-left: 10px; padding-right: 10px;'><form name=img_form onSubmit='return false'><br>"+text_enter_image+"<br><input type='url' style='width:98%; margin:4px 0px' maxlength='255' name='img_url' id='img_url' value='"+url+"' class='forminput'></form></div></div></div>";
  var newDiv = document.createElement('div');
  newDiv.id='img_dialog';
  newDiv.innerHTML = text;
  document.body.appendChild(newDiv);
  $(function(){
    $('#img_dialog').jqcd({
      width: 420,
      height: 130,
      title_text: text_img,
      buttons_position: 'center',
      position: 'center' 
    });

    $('#img_dialog').jqcd_open();

    function OKBtn()
    {
      if (selection_range) 
      {
        selection_range.select();
        document.forms.REPLIER.Post.focus();
    }
      url = document.forms.img_form.img_url.value;
      if (url=="http://") url="";
      doInsert("[IMG]"+url+"[/IMG]", "", false);
      $('#img_dialog').jqcd_close();
    }
    $('#img_dialog').jqcd_add_button('img_ok_button', 'OK',true,OKBtn); 
    $('#img_dialog').keypress(function(event)
    { 
      if (event.which == 13) 
      {
        OKBtn(); 
	return false;
    }
    });
    $('#img_dialog').jqcd_add_button('img_cancel_button', text_cancel, false,function() {$('#img_dialog').jqcd_close(); });
    $('#img_url').select();
    $('#img_url').focus();
 });

}


function tag_email()
{
    var FoundErrors = '';
    var enterEMAIL   = prompt(text_enter_email, "");
    var enterNAME = prompt(text_enter_email_name, "");

    if (!enterEMAIL) {
        FoundErrors += " " + error_no_email;
    }
    if (!enterNAME) {
        FoundErrors += " " + error_no_email_name;
    }

    if (FoundErrors) {
        alert("Error!"+FoundErrors);
        return;
    }

    doInsert("[URL=mailto:"+enterEMAIL+"]"+enterNAME+"[/URL]", "", false);
}

function tag_attach(num) {
	if (doInsert('[attach=#'+num+']', '[/attach]', true)) {
		doInsert('[/attach]', '', false)
	}
	return false;
}

function tag_spoiler()
{
  if (Spoiler_open > 0) 
  {
		// Find the last occurance of the opened tag
		lastindex = 0;
		for (i = 0 ; i < bbtags.length; i++ )
		{
			if ( bbtags[i] == 'Spoiler' )
			{
				lastindex = i;
			}
		}
		
		// Close all tags opened up to that tag was opened
		while (bbtags[lastindex])
		{
			tagRemove = popstack(bbtags);
			doInsert("[/" + tagRemove + "]", "", false)
                  // Change the button status
			if ( (tagRemove != 'FONT') && (tagRemove != 'SIZE') && (tagRemove != 'COLOR') )
			{
				eval("document.REPLIER." + tagRemove + ".value = ' " + tagRemove + " '");
				eval(tagRemove + "_open = 0");
			}
		}	
                cstat();
	} else 
        {
    if (document.selection) var selection_range=document.selection.createRange();
            if (selection_range) 
	    {
	      selection_range.select();
	      document.forms.REPLIER.Post.focus();
	    }
    if (doInsert('[Spoiler]', '[/Spoiler]', true)) 
    {
		    Spoiler_open ++;
	    	document.REPLIER.Spoiler.value += ' *';
			pushstack(bbtags, 'Spoiler');
			cstat();
            }
  }
	    }
 
function tag_spoiler_dialog()
{
  if (Spoiler_open > 0) 
  {
    // Find the last occurance of the opened tag
    lastindex = 0;
    for (i = 0 ; i < bbtags.length; i++ )
    {
      if ( bbtags[i] == 'Spoiler' )
      {
        lastindex = i;
      }
    } 
 		
    // Close all tags opened up to that tag was opened
    while (bbtags[lastindex])
    {
      tagRemove = popstack(bbtags);
      doInsert("[/" + tagRemove + "]", "", false)
      // Change the button status
      if ( (tagRemove != 'FONT') && (tagRemove != 'SIZE') && (tagRemove != 'COLOR') )
      {
        eval("document.REPLIER." + tagRemove + ".value = ' " + tagRemove + " '");
        eval(tagRemove + "_open = 0");
      }
    }	
    cstat();
  } else 
  {
        if (last=document.getElementById('spl_dialog')) document.body.removeChild(last);
	if (document.selection) var selection_range=document.selection.createRange();
        var enterText='';
        text = "<div class='jqcd_dialog'><div class='jqcd_content_layer'><div class='jqcd_content' style='padding-left: 10px; padding-right: 10px;'><form name=spl_form onSubmit='return false'><br>"+text_enter_spoiler+"<br><input type='text' style='width:98%; margin:4px 0px' maxlength='255' name='spl_text' id='spl_text' value='"+enterText+"' class='forminput'></form></div></div></div>";
        var newDiv = document.createElement('div');
        newDiv.id='spl_dialog';
        newDiv.innerHTML = text;
        document.body.appendChild(newDiv);
        $(function(){
          $('#spl_dialog').jqcd({
            width: 420,
            height: 130,
            title_text: text_spoiler,
            buttons_position: 'center',
            position: 'center' 
          });
          
          $('#spl_dialog').jqcd_open();
          
          function OKBtn()
          {
            enterText = document.forms.spl_form.spl_text.value;
	    var openTag = '[Spoiler]';
        if (enterText && enterText != text_spoiler_hidden_text) 
        {
	    	openTag = '[Spoiler=' + enterText + ']';
	    }
	    if (selection_range) 
	    {
              selection_range.select();
	      document.forms.REPLIER.Post.focus();
	    }
        if (doInsert(openTag, '[/Spoiler]', true)) 
        {
		    Spoiler_open ++;
	    	    document.REPLIER.Spoiler.value += ' *';
		    pushstack(bbtags, 'Spoiler');
		    cstat();
	    }
            $('#spl_dialog').jqcd_close();
	}
          $('#spl_dialog').jqcd_add_button('spl_ok_button', 'OK',true,OKBtn); 
      $('#spl_dialog').keypress(
        function(event)
	  { 
	    if (event.which == 13) 
	    {
	      OKBtn(); 
	      return false;
}
	  });
          $('#spl_dialog').jqcd_add_button('spl_cancel_button', text_cancel, false,function() {$('#spl_dialog').jqcd_close(); });
          $('#spl_text').focus();
       });
}
}

function tag_quote()
{
  if (QUOTE_open > 0) 
  {
	        // Find the last occurance of the opened tag
		lastindex = 0;
		for (i = 0 ; i < bbtags.length; i++ )
		{
			if ( bbtags[i] == 'QUOTE' )
			{
				lastindex = i;
			}
		}
		// Close all tags opened up to that tag was opened
		while (bbtags[lastindex])
		{
			tagRemove = popstack(bbtags);
			doInsert("[/" + tagRemove + "]", "", false)
                  // Change the button status
			if ( (tagRemove != 'FONT') && (tagRemove != 'SIZE') && (tagRemove != 'COLOR') )
			{
				eval("document.REPLIER." + tagRemove + ".value = ' " + tagRemove + " '");
				eval(tagRemove + "_open = 0");
			}
		}	
                cstat();
        } else
        {
    if (document.selection) var selection_range=document.selection.createRange();
            if (selection_range) 
	    {
	      selection_range.select();
	      document.forms.REPLIER.Post.focus();
	    }
    if (doInsert('[QUOTE]', '[/QUOTE]', true)) 
    {
		    QUOTE_open ++;
	    	document.REPLIER.QUOTE.value += ' *';
			pushstack(bbtags, 'QUOTE');
			cstat();
	    }
	}
}



function tag_quote_dialog()
{
  if (QUOTE_open > 0) 
  {
    // Find the last occurance of the opened tag
    lastindex = 0;
    for (i = 0 ; i < bbtags.length; i++ )
    {
      if ( bbtags[i] == 'QUOTE' )
      {
        lastindex = i;
      }
    }
    // Close all tags opened up to that tag was opened
    while (bbtags[lastindex])
    {
      tagRemove = popstack(bbtags);
      doInsert("[/" + tagRemove + "]", "", false)
      // Change the button status
      if ( (tagRemove != 'FONT') && (tagRemove != 'SIZE') && (tagRemove != 'COLOR') )
      {
      eval("document.REPLIER." + tagRemove + ".value = ' " + tagRemove + " '");
      eval(tagRemove + "_open = 0");
      }
    }	
    cstat();
        }  else
        {
        if (last=document.getElementById('qt_dialog')) document.body.removeChild(last);
	if (document.selection) var selection_range=document.selection.createRange();
        var enterText='';
        text = "<div class='jqcd_dialog'><div class='jqcd_content_layer'><div class='jqcd_content' style='padding-left: 10px; padding-right: 10px;'><form name=qt_form onSubmit='return false'><br>"+text_enter_quote+"<br><input type='text' style='width:98%; margin:4px 0px' maxlength='255' name='qt_text' id='qt_text' value='"+enterText+"' class='forminput'></form></div></div></div>";
        var newDiv = document.createElement('div');
        newDiv.id='qt_dialog';
        newDiv.innerHTML = text;
        document.body.appendChild(newDiv);
        $(function(){
          $('#qt_dialog').jqcd({
            width: 420,
            height: 130,
            title_text: text_quote,
            buttons_position: 'center',
            position: 'center' 
          });
          
          $('#qt_dialog').jqcd_open();
          
          function OKBtn()
          {
            enterText = document.forms.qt_form.qt_text.value;
	    var openTag = '[QUOTE]';
	    if (enterText) {
	    	openTag = '[QUOTE=' + enterText + ']';
	    }
            if (selection_range) 
	    {
	      selection_range.select();
	      document.forms.REPLIER.Post.focus();
	    }
        if (doInsert(openTag, '[/QUOTE]', true)) 
        {
		    QUOTE_open ++;
	    	document.REPLIER.QUOTE.value += ' *';
			pushstack(bbtags, 'QUOTE');
			cstat();
	    }
            $('#qt_dialog').jqcd_close();
          }
          $('#qt_dialog').jqcd_add_button('qt_ok_button', 'OK',true,OKBtn); 
          $('#qt_dialog').keypress(function(event)
          { 
            if (event.which == 13) 
	    {
	      OKBtn(); 
              return false;
	    }
	  });
          $('#qt_dialog').jqcd_add_button('qt_cancel_button', text_cancel, false,function() {$('#qt_dialog').jqcd_close(); });
          $('#qt_text').focus();
       });
	}
}



//--------------------------------------------
// GENERAL INSERT FUNCTION
//--------------------------------------------
// ibTag: opening tag
// ibClsTag: closing tag, used if we have selected text
// isSingle: true if we do not close the tag right now
// return value: true if the tag needs to be closed later

function doInsert(ibTag, ibClsTag, isSingle)
{
    var isClose = false;
    var obj_ta = document.REPLIER.Post;

//alert("doInsert started");

    if ( document.selection && document.selection.createRange )
    {
            obj_ta.focus();
            sel1 = obj_ta. value. substr(0, obj_ta.selectionStart);

            var sel = document.selection;
            var text = ibTag;
            var rng = sel.createRange();

            if ( (sel.type == "Text" || sel.type == "None") && rng != null ) 
            {
                if ( ibClsTag != "" && rng.text.length > 0) ibTag += rng.text + ibClsTag; else 
                 if ( isSingle) isClose = true;

                if ( ibClsTag == "[/CODE]" && rng.text.length == 0 ) ibTag += "\n\n" + ibClsTag;

                rng.text = ibTag;
            }

	    selPos = text.length + sel1.length;

	    if ( myAgent.indexOf("opera") != -1 ) obj_ta. setSelectionRange(selPos, selPos);

    } else if ((is_nav && (myVersion > 4)) || is_webkit) 
    {
    	var selStart = obj_ta.selectionStart;
    	var selEnd = obj_ta.selectionEnd;

        sel1 = obj_ta. value. substr(0, obj_ta.selectionStart);
        sel2 = obj_ta. value. substr(obj_ta.selectionEnd, obj_ta. value. length - obj_ta.selectionEnd);
        sel = obj_ta. value. substr(obj_ta.selectionStart, 
                    obj_ta.selectionEnd - obj_ta.selectionStart);
        var text = ibTag;
        if (ibClsTag.length && sel.length) text += sel + ibClsTag;
        else if (isSingle) isClose = true;
        if (ibClsTag == "[/CODE]" && !sel.length) text += "\n\n" + ibClsTag;

        obj_ta. value = sel1 + text + sel2;
        
        obj_ta.focus();
        inner_length = sel.length;
        if (!ibClsTag.length) inner_length=0;
        obj_ta. setSelectionRange(selStart + ibTag.length, selStart + ibTag.length + inner_length );
         
    } else
    {
        if ( isSingle ) isClose = true;

        if ( ibClsTag == "[/CODE]" ) ibTag += "\n\n" + ibClsTag;

        obj_ta.value += ibTag;

        obj_ta.focus();

    }

    return isClose;
}

var txt='' 

function get_name(name) 
{ 
  txt=name;
}

function get_selection(qname, qdate, pid) 
{ 
  txt='' 
  if (document.getSelection) { txt = document.getSelection(); }
  else if (document.selection) { txt = document.selection.createRange().text; } 
  else if (window.getSelection) {txt = window.getSelection().toString()}
  if ( txt ) txt='[quote='+qname+','+qdate+','+pid+']'+txt+'[/quote]\n';
}


$( document ).ready(function() {
	var clickEventType=((document.ontouchstart!==null)?'click':'touchstart');
    $('.quick-quote').bind(clickEventType, function(e) {
        var el = $( this );
        get_selection(el.data("qname"), el.data("qdate"), el.data("post-id"));
        Insert();
        e.preventDefault();
    });
});

function getSelectedText(obj)
{
 obj.focus();
 if (document.selection) 
 {
   var s = document.selection.createRange(); 
   if (s.text) return s.text;
 }
 else if (typeof(obj.selectionStart)=="number")
 {
   if (obj.selectionStart!=obj.selectionEnd)
   {
     var start = obj.selectionStart;
     var end = obj.selectionEnd;

     return obj.value.substr(start,end-start);
     obj.setSelectionRange(end,end);
   }
 }
 return '';
}

function Insert()
{ 
   if ( txt )
    if ( scroll_to ) doInsert(txt, "", false); else
    {
       var input = document.REPLIER.Post;

       if (input.value != '') { input.value = input.value + '\n' };

       input.value = input.value + txt;
    }
}
