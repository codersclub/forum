function PopUp(url, name, width,height,center,resize,scroll,posleft,postop) {
	if (posleft != 0) { x = posleft }
	if (postop  != 0) { y = postop  }

	if (!scroll) { scroll = 1 }
	if (!resize) { resize = 1 }

	if ((parseInt (navigator.appVersion) >= 4 ) && (center)) {
	  X = (screen.width  - width ) / 2;
	  Y = (screen.height - height) / 2;
	}
	if (scroll != 0) { scroll = 1 }

	var Win = window.open( url, name, 'width='+width+',height='+height+',top='+Y+',left='+X+',resizable='+resize+',scrollbars='+scroll+',location=no,directories=no,status=no,menubar=no,toolbar=no');
}
function redirect_to(where, closewin)
{
	  opener.location= '$ibforums->base_url' + where;
	  
	  if (closewin == 1)
	  {
		  self.close();
	  }
}
