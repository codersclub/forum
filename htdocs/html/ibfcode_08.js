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
var MOD_open = 0;
var EX_open = 0;
var MM_open = 0;
var GM_open = 0;
var doHTML_open = 0;
var SF_open = 0;
var ST_open = 0;
var SALL_open = 0;
var STALL_open = 0;
var QUOTE_open = 0;
var CODE_open = 0;
var SQL_open = 0;
var HTML_open = 0;

var bbtags   = new Array();

// Determine browser type and stuff.
// Borrowed from http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html

var myAgent   = navigator.userAgent.toLowerCase();
var myVersion = parseInt(navigator.appVersion);

var is_ie   = ((myAgent.indexOf("msie") != -1)  && (myAgent.indexOf("opera") == -1));
var is_nav  = ((myAgent.indexOf('mozilla')!=-1) && (myAgent.indexOf('spoofer')==-1)
                && (myAgent.indexOf('compatible') == -1) && (myAgent.indexOf('opera')==-1)
                && (myAgent.indexOf('webtv') ==-1)       && (myAgent.indexOf('hotjava')==-1));

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
	var listvalue = "init";
	var thelist = "";
	
	while ( (listvalue != "") && (listvalue != null) )
	{
		listvalue = prompt(list_prompt, "");
		if ( (listvalue != "") && (listvalue != null) )
		{
			thelist = thelist+"[*]"+listvalue+"\n";
		}
	}
	
	if ( thelist != "" )
	{
		doInsert( "[LIST]\n" + thelist + "[/LIST]\n", "", false);
	}
}

function tag_url()
{
    var FoundErrors = '';
    var enterURL   = prompt(text_enter_url, "http://");
    var enterTITLE = prompt(text_enter_url_name, "My Webpage");

    if (!enterURL) {
        FoundErrors += " " + error_no_url;
    }
    if (!enterTITLE) {
        FoundErrors += " " + error_no_title;
    }

    if (FoundErrors) {
        alert("Error!"+FoundErrors);
        return;
    }

	doInsert("[URL="+enterURL+"]"+enterTITLE+"[/URL]", "", false);
}

function tag_image()
{
    var FoundErrors = '';
    var enterURL   = prompt(text_enter_image, "http://");

    if (!enterURL) {
        FoundErrors += " " + error_no_url;
    }

    if (FoundErrors) {
        alert("Error!"+FoundErrors);
        return;
    }

	doInsert("[IMG]"+enterURL+"[/IMG]", "", false);
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

    if ( document.selection && document.selection.createRange )
    {
            obj_ta.focus();
            sel1 = obj_ta. value. substr(0, obj_ta.selectionStart);

            var sel = document.selection;
            var text = ibTag;
            var rng = sel.createRange();

            rng.colapse;

            if ( (sel.type == "Text" || sel.type == "None") && rng != null ) 
            {
                if ( ibClsTag != "" && rng.text.length > 0) ibTag += rng.text + ibClsTag; else 
                 if ( isSingle) isClose = true;

                if ( ibClsTag == "[/CODE]" && rng.text.length == 0 ) ibTag += "\n\n" + ibClsTag;

                rng.text = ibTag;
            }

	    selPos = text.length + sel1.length;

	    if ( myAgent.indexOf("opera") != -1 ) obj_ta. setSelectionRange(selPos, selPos);

    } else if (is_nav && (myVersion > 4)) 
    {
        obj_ta. focus ();
        sel1 = obj_ta. value. substr(0, obj_ta.selectionStart);
        sel2 = obj_ta. value. substr(obj_ta.selectionEnd, obj_ta. value. length - obj_ta.selectionEnd);
        sel = obj_ta. value. substr(obj_ta.selectionStart, 
                    obj_ta.selectionEnd - obj_ta.selectionStart);
        var text = ibTag;
        if (ibClsTag.length && sel.length) text += sel + ibClsTag;
        else if (isSingle) isClose = true;
        if (ibClsTag == "[/CODE]" && !sel.length) text += "\n\n" + ibClsTag;

        obj_ta. value = sel1 + text + sel2;
        
        selPos = text.length + sel1.length;
        obj_ta. setSelectionRange(selPos, selPos);

    } else
    {
        if ( isSingle ) isClose = true;

        if ( ibClsTag == "[/CODE]" ) ibTag += "\n\n" + ibClsTag;

        obj_ta.value += ibTag;

    }

    obj_ta.focus();
    
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
  if (document.getSelection) { txt = document.getSelection(); } else if (document.selection) { txt = document.selection.createRange().text; } 
  if ( txt ) txt='[quote='+qname+','+qdate+','+pid+']'+txt+'[/quote]\n';
}

function Insert(scroll)
{ 
   if ( txt )
    if ( scroll ) doInsert(txt, "", false); else
    {
       var input = document.REPLIER.Post;

       if (input.value != '') { input.value = input.value + '\n' };

       input.value = input.value + txt;
    }
}