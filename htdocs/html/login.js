/**
 *
 */
function ValidateForm() {
	var Check = 0;
	// if (document.LOGIN.UserName.value == '') { Check = 1; }
	// if (document.LOGIN.PassWord.value == '') { Check = 1; }

	if (Check == 1) {
		alert(blank_fields);
		return false;
	} else {
		document.LOGIN.submit.disabled = true;
		return true;
	}
}

function onAuthMethodChange() {
	var auth_selector = 'tr[name^=auth_'+ document.LOGIN.auth_method.value + ']';
	var trs = $('tr[name^=auth_]');

	trs.not(auth_selector).hide();
	trs.filter(auth_selector).show();
	/*
	if ( == 'password') {
		$('tr[name=auth_openid]').hide();
		$('#auth_password').show();
	} else {
		$('#auth_openid').show();
		$('#auth_password').hide();
	}
	*/
}

$(window).load(function() {onAuthMethodChange();});
