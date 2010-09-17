var url_input      = "";
var url_input2      = "";
var remove_pressed = 0;

function clear_it()
{
	if ( document.bob.url_photo.value != "" )
	{
		url_input = document.bob.url_photo.value;
	}

	document.bob.url_photo.value = "";
}
function restore_it()
{
	if (url_input != "")
	{
		document.bob.url_photo.value = url_input;
	}
}
function checkform()
{
	if ( remove_pressed != 1 )
	{
		return true;
	} else
	{
		fcheck = confirm(pp_confirm);
		if ( fcheck == true )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
function CheckLength(Type) {
    LocationLength  = document.theForm.Location.value.length;
    InterestLength  = document.theForm.Interests.value.length;
    message  = "";
    if (Type == "location") {
        if (max_location_length != 0) {
            message = js_location + ": " + js_max + " " + max_location_length + " " + js_characters + ".";
        } else {
            message = "";
        }
        alert(message + "  " + js_used + " " + LocationLength + " " + js_so_far + ".");
    }
    if (Type == "interest") {
        if (max_interest_length != 0) {
            message = js_interests + ": " + js_max + " " + max_interest_length + " " + js_characters + ".";
        } else {
            message = "";
        }
        alert(message + "  " + js_used + " " + InterestLength + " " + js_so_far + ".");
    }
    
}
function ValidateProfile() {
    LocationLength  = document.theForm.Location.value.length;
    InterestLength  = document.theForm.Interests.value.length;
    errors = "";
    if (max_location_length !=0) {
        if (LocationLength > max_location_length) {
            errors = js_location + ":  " + js_max + " " + max_location_length + " " + js_characters + ".  " + js_used + ": " + LocationLength;
        }
    }
    if (max_interest_length !=0) {
        if (InterestLength > max_interest_length) {
            errors = errors + "  " + js_interests + ":  " + js_max + " " + max_interest_length + " " + js_characters + ".  " + js_used + ": " + InterestLength;
        }
    } 
    
    if (errors != "") {
        alert(errors);
        return false;
    } else {
        return true;
    }
}
function restore_it()
{
	if (url_input2 != "")
	{
		document.creator.url_avatar.value = url_input2;
	}
}
function select_url() { restore_it(); }
function select_upload() {
  
  try {
	  if ( document.creator.url_avatar.value != "" ) { url_input2 = document.creator.url_avatar.value; }
	  document.creator.url_avatar.value = "";
  }
  catch(nourl) {
  	  return true;
  }
}
function checkform()
{
	if ( remove_pressed != 1 )
	{
		return true;
	} else
	{
		fcheck = confirm(av_confirm);

		if ( fcheck == true )
		{
			return true;
		} else
		{
			return false;
		}
	}
}
function CheckLength2() {
    MessageLength  = document.REPLIER.Post.value.length;
    message  = "";
        if (MessageMax > 0) {
            message = js_max_length + " " + MessageMax + " " + js_characters + ".";
        } else {
            message = "";
        }
        alert(message + " " + js_used + " " + MessageLength + " " + js_characters + ".");
}
function do_smile_preview() {

        var f = document.prefs.u_sskin;

        if (f.options[f.selectedIndex].value == -1) { return false; }

        window.open( 'index.php?act=legends&CODE=emoticons&s=&sskin=' + f.options[f.selectedIndex].value,'Legends','width=250,height=500,resizable=yes,scrollbars=yes')

}	
function do_preview() {
	
	var f = document.prefs.u_skin;
	
	if (f.options[f.selectedIndex].value == -1) { return false; }
	
	window.open(js_base_url + 'skinid=' + f.options[f.selectedIndex].value, 'Preview', 'width=800,height=600,top=0,left=0,resizable=1,scrollbars=1,location=no,directories=no,status=no,menubar=no,toolbar=no');
	
}
function CheckAll(cb) {
	  var fmobj = document.mutliact;
	  for (var i=0;i<fmobj.elements.length;i++) {
		  var e = fmobj.elements[i];
		  if ((e.name != 'allbox') && (e.type=='checkbox') && (!e.disabled)) {
			  e.checked = fmobj.allbox.checked;
		  }
	  }
}
function CheckCheckAll(cb) {	
	  var fmobj = document.mutliact;
	  var TotalBoxes = 0;
	  var TotalOn = 0;
	  for (var i=0;i<fmobj.elements.length;i++) {
		  var e = fmobj.elements[i];
		  if ((e.name != 'allbox') && (e.type=='checkbox')) {
			  TotalBoxes++;
			  if (e.checked) {
				  TotalOn++;
			  }
		  }
	  }
	  if (TotalBoxes==TotalOn) {fmobj.allbox.checked=true;}
	  else {fmobj.allbox.checked=false;}
}
function do_msg(msg) {
   if ( msg != "" )
   {
	   alert(msg);
   }
}