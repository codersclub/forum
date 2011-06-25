/**
 * 
 */
function ValidateForm() {
	var Check = 0;
	if (document.LOGIN.UserName.value == '') { Check = 1; }
	// if (document.LOGIN.PassWord.value == '') { Check = 1; }

	if (Check == 1) {
		alert("{$ibforums->lang['blank_fields']}");
		return false;
	} else {
		document.LOGIN.submit.disabled = true;
		return true;
	}
}

function onAuthMethodChange() {
	if (document.LOGIN.auth_method.value == 'password') {
		$('#auth_openid').hide();
		$('#auth_password').show();
	} else {
		$('#auth_openid').show();
		$('#auth_password').hide();		
	}
}