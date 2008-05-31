// Form validations

function checkfrm_search() {
	var messg = "";
	
	if (document.forms['searchform'].s.value == "")
		messg += "Please enter your keyword(s) to perform a search";

	if (messg=='') {
		return true;
	}
	else {
		alert(messg);
		return false;
	}
}

function checkfrm_contact() {
	var messg = "";
	var messg2 = "";

	if (document.forms['contactform'].name.value=='')
		messg += "Name\n";
	//if ((document.forms['contact'].contactnum.value=='') || (document.forms['contact'].contactnum.value=='your contact number'))
		//messg += "your contact number\n";
	if (document.forms['contactform'].message.value=='')
		messg += "Message\n";

	if (!validEmail(document.forms['contactform'].email.value))
		messg2 += "\nPlease provide a valid E-mail address (eg. name@example.com)";

	if ((messg=='') && (messg2=='')) {
		return true;
	}
	else {
		if (messg == '')
			alert(messg2);
		else {
			messg = "You have left the following required fields blank or\n" + "incomplete, please correct them before continuing:\n\n" + messg + messg2;
			alert(messg);
		}
		return false;
	}
}

function checkfrm_comment() {
	var messg = "";
	var messg2 = "";

	if (document.forms['commentform'].author.value == "")
		messg += "Name \n";
	if (document.forms['commentform'].comment.value == "")
		messg += "Comment \n";

	if (!validEmail(document.forms['commentform'].email.value))
		messg2 += "\nPlease provide a valid E-mail address (eg. name@example.com)";

	if ((messg=='') && (messg2=='')) {
		return true;
	}
	else {
		if (messg == '')
			alert(messg2);
		else {
			messg = "You have left the following required fields blank or\n" + "incomplete, please correct them before continuing:\n\n" + messg + messg2;
			alert(messg);
		}
		return false;
	}
}

// copyright 1999 Idocs, Inc. http://www.idocs.com
// Distribute this script freely but keep this notice in place
function numbersonly(myfield, e, dec) {
var key;
var keychar;

if (window.event)
   key = window.event.keyCode;
else if (e)
   key = e.which;
else
   return true;

keychar = String.fromCharCode(key);

// control keys and punctuation
if ((key==null) || (key==0) || (key==8) || (key==9) || (key==13) || (key==27) || (key==45) || (key==46) || (key==120) || (key==45) || (key==40) || (key==41) || (key==32) || (key==43))
   return true;

// numbers
else if ((("0123456789").indexOf(keychar) > -1))
   return true;

// decimal point jump
else if (dec && (keychar == ".")) {
   myfield.form.elements[dec].focus();
   return false;
}
else return false;
}

function validEmail(email) {
	invalidChars = " /:,;";
	if (email == "") {
		return false;
	}
	for (i=0; i<invalidChars.length; i++) {
		badChar = invalidChars.charAt(i);
		if (email.indexOf(badChar,0) != -1) {
			return false;
		}
	}
	atPos = email.indexOf("@",1);
	if (atPos == -1) {
		return false;
	}
	if (email.indexOf("@",atPos+1) != -1) {
		return false;
	}
	periodPos = email.indexOf(".",atPos);
	if (periodPos == -1) {
		return false;
	}
	if (periodPos+3 > email.length)	{
		return false;
	}
	return true;
}

function replace(string,text,by) {
// Replaces text with by in string
    var strLength = string.length, txtLength = text.length;
    if ((strLength == 0) || (txtLength == 0)) return string;

    var i = string.indexOf(text);
    if ((!i) && (text != string.substring(0,txtLength))) return string;
    if (i == -1) return string;

    var newstr = string.substring(0,i) + by;

    if (i+txtLength < strLength)
        newstr += replace(string.substring(i+txtLength,strLength),text,by);

    return newstr;
}