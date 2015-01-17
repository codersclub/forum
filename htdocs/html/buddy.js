function redirect_to(where, closewin) {
    opener.location = base_url + where;

    if (closewin == 1) {
        self.close();
    }
}

function check_form(helpform) {
    opener.name = "ibfmain";

    if (helpform == 1) {
        document.theForm2.target = 'ibfmain';
    } else {
        document.theForm.target = 'ibfmain';
    }

    return true;
}

function shrink() {
    window.resizeTo('200', '75');
}

function expand() {
    window.resizeTo('200', '450');
}

