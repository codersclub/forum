// С пламенным приветом Тупаку 'Ыйцо' Шакуру и FORUMS.AG.RU
// Copyright (c) 2003 by UriSoft


var dom = (document.getElementById) ? true : false;
var nn4 = (document.layers) ? true : false;
var ie = (document.all) ? true : false;
var ie4 = (!dom && ie) ? true : false;
var moz = (dom && (navigator.appName=="Netscape")) ? true : false;
var opr = (dom && window.opera) ? true : false;
var op7 = (opr && (navigator.userAgent.indexOf("Opera 7") > 0 || navigator.userAgent.indexOf("Opera/7") >= 0)) ? true : false;

var hintsObj = null;


// Netscape, Mozilla and Opera
function moveHintsN(e) {

	if (hintsObj == null) return;
	if (nn4) {
		document.layers['hints'].left = e.pageX + 10;
		document.layers['hints'].top = e.pageY + 21;
		document.layers['hints'].visibility = "show"
	} else if (moz || op7) {
		document.getElementById('hints').style.left = window.pageXOffset + e.clientX + 10 + "px";
		document.getElementById('hints').style.top = window.pageYOffset + e.clientY + 21 + "px";
		document.getElementById('hints').style.visibility = "visible";
	}
}

function hideHintsN(e) {

	if (hintsObj == null) return;
	document.releaseEvents(Event.MOUSEMOVE);
	document.releaseEvents(Event.MOUSEOUT);
	document.onmousemove = null;
	document.onmouseout = null;
   	if (nn4) {
		document.layers['hints'].visibility = "hide";
		document.layers['hints'].left = 0;
		document.layers['hints'].top = 0;
    } else if (moz || op7) document.getElementById('hints').style.visibility = "hidden";
    hintsObj = null;
}


// IE
function moveHints() {

	if (opr || moz || nn4 || (hintsObj == null)) return;

    xoff = 0;
    yoff = 0;
	if (dom || ie) {
		if (dom) q = document.getElementById(hintsObj);
		else q = document.all[hintsObj];
		while (q) {
			xoff += q.offsetLeft;
			yoff += q.offsetTop;
			q = q.offsetParent;
		}
		xoff += window.event.offsetX + 10;
		yoff += window.event.offsetY + 21;
	}

    if (dom) {
		document.getElementById('hints').style.left = xoff;
		document.getElementById('hints').style.top = yoff;  
	} else if (ie4) {
		document.all['hints'].style.left = xoff;
		document.all['hints'].style.top = yoff;
	}
}

function hideHints() {

	if (opr || moz || nn4 || (hintsObj == null)) return;

	if (dom) document.getElementById('hints').style.visibility = "hidden";
		else if (ie4) document.all['hints'].style.visibility = "hidden";
    hintsObj = null;
}


// ALL
function showHints(obj,head,text) {

	if ((opr && !op7) || (text == "")) return;

    contents = "<table border=0 cellspacing=0 cellpadding=1 width=300 class=\'hintshead\'><tr><td><b>" + head + "</b></td></tr><tr><td><table border=0 cellspacing=0 cellpadding=3 width=100% class=\'hintstext\'><tr><td>" + text + "</td></tr></table></td></tr></table>";

	hintsObj = obj;
	if (dom) document.getElementById('hints').innerHTML = contents;
	else if (nn4) {
			document.layers['hints'].document.open();
			document.layers['hints'].document.write(contents);
			document.layers['hints'].document.close();
		}
	else if (ie4) document.all['hints'].innerHTML = contents;

    // for Netscape, Mozilla and Opera
	if (nn4 || moz || op7) {
		document.captureEvents(Event.MOUSEMOVE);
		document.captureEvents(Event.MOUSEOUT);
		document.onmousemove = moveHintsN;
		document.onmouseout = hideHintsN;
		return;
	}
    // for IE
    if (dom || ie) {
		moveHints();
		if (dom) document.getElementById('hints').style.visibility = "visible";
			else if (ie4) document.all['hints'].style.visibility = "visible";
    }
}

