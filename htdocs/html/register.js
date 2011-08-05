function ValidateLostPass() {
	// Check for Empty fields
	if (document.REG.uid.value == "" || document.REG.aid.value == "") {
		alert (js_blanks);
		return false;
	}
}

function Validate() {
	// Check for Empty fields
	if (document.REG.UserName.value == "" || document.REG.PassWord.value == "" || document.REG.PassWord_Check.value == "" || document.REG.EmailAddress.value == "") {
		alert (js_blanks);
		return false;
	}
	if (passwordsIsEquals()) {
		// return false;
	}
	emailsIsEquals();
	// Have we checked the checkbox?

	if (document.REG.agree.checked == true) {
		return true;
	} else {
		alert (js_no_check);
		return false;
	}
}

function passwordsIsEquals() {
	
	if (!document.REG.PassWord_Check.setCustomValidity) {
		return true;
	}
		
	if (document.REG.PassWord.value != document.REG.PassWord_Check.value) {
		document.REG.PassWord_Check.setCustomValidity(js_err_pass_match);
		return false;
	} else {
		document.REG.PassWord_Check.setCustomValidity('');
		return;
	}

}

function emailsIsEquals() {
	if (!document.REG.EmailAddress.setCustomValidity) {
		return true;
	}
		
	if (document.REG.EmailAddress.value != document.REG.EmailAddress_two.value) {
		document.REG.EmailAddress_two.setCustomValidity(js_err_email_address_match);
		return false;
	} else {
		document.REG.EmailAddress.setCustomValidity('');
		return;
	}
	
}

