/*
 * $RCSfile
 *
 * phpBaseClasses - Foundation for any application in php
 * Copyright (C) 2002-2003 Russell Chadwick
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * @version $Revision: 1.6 $ $Date: 2004/06/03 08:02:28 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

function openNewWindow (URL, name, features) {
	window.open (URL, name, features).focus ();
}

function generatePassword () {
	var characters = "23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
	var passwordlength = 8;

	var pass = '';
	var randomNumber = 0;

	for (var n = 1; n <= passwordlength; n++) {
		randomNumber = Math.floor (characters.length * Math.random ());
		pass += characters.substring (randomNumber, randomNumber + 1);
	}

	return pass;
}

function findObject (n, d) {
	var p, i, x;

	if (!d)
		d=document;

	if (!(x = d[n]) && d.all)
		x = d.all[n];

	for (i = 0; !x && i < d.forms.length; i++)
		x = d.forms[i][n];

	for (i = 0; !x && d.layers && i < d.layers.length; i++)
		x = findObject (n, d.layers[i].document);

	if (!x && d.getElementById)
		x = d.getElementById(n);

	return x;
}

function findParentObject (n, d) {
	var p, i, x;

	if (!d)
		d=opener.parent.document;

	if (!(x = d[n]) && d.all)
		x = d.all[n];

	for (i = 0; !x && i < d.forms.length; i++)
		x = d.forms[i][n];

	for (i = 0; !x && d.layers && i < d.layers.length; i++)
		x = findParentObject (n, d.layers[i].document);

	if (!x && d.getElementById)
		x = d.getElementById(n);

	return x;
}

function setValue (n, v) {
	var x;

	x = findObject (n);

	x.value = v;
}

function setChecked (n, v) {
	var x;

	x = findObject (n);

	x.checked = v;
}

function validateForm (submit, message) {
	var x;

	x = findObject (submit);

	if (message == "") {
		x.click ();
	}
}

function populatePassword (n1, n2) {
	var x1, x2, passs;

	x1 = findObject (n1);
	x2 = findObject (n2);

	pass = generatePassword ();

	x1.value = pass;
	x2.value = pass;

	alert ('Write the password down and keep it somewhere safe.  The password generated is: ' + pass);
}

function hex2decimal (n) {
	var x, test, retval;

	retval = 0;

	for (x = n.length; x > 0; x--) {
		test = n.substr ((x - 1), 1);
		test = test.toUpperCase();
		retval += (Math.pow (16, (n.length - x))) * ("0123456789ABCDEF".indexOf (test));
	}

	return retval;
}

function displayError (n, error_message) {
	var x;

	x = findObject (n + '_title');
	error_message = x.innerHTML + error_message;

	x = findObject (n + '_error_message');
	x.innerHTML = '<br>' + error_message;

	x = findObject (n + '_error_image');
	x.style.visibility = 'visible';

	return '\n' + error_message;
}

function clearError (n) {
	var x;

	x = findObject (n + '_error_message');
	x.innerHTML = '';

	x = findObject (n + '_error_image');
	x.style.visibility = 'hidden';

	return '';
}

function validateDigits (d) {
	return validateCharacterSet (d, "-0123456789");
}

function validateCharacterSet (d, charset) {
	for (var i = 0; i < d.length; i++) {
		if (charset.indexOf (d.substr (i, 1)) < 0) {
			return false;
		}
	}

	return true;
}

function validateRange (d, min, max) {
	if (d < min || d > max) {
		return false;
	} else {
		return true;
	}
}

function validateMinimumLength (d, min) {
	if (d.substr (0, 1) == "-") {
		d = d.substr (1, (d.length - 1));
	}

	if (min != 0) {
		if (d.length < min) {
			return false;
		}
	}

	return true;
}

function validateMaximumLength (d, max) {
	if (d.substr (0, 1) == "-") {
		d = d.substr (1, (d.length - 1));
	}

	if (max != 0) {
		if (d.length > max) {
			return false;
		}
	}

	return true;
}

function validateHex (d) {
	return validateCharacterSet (d, "0123456789abcdefABCDEF");
}

function validateMatching (n1, n2) {
	var x1, x2;

	x1 = findObject (n1);
	x2 = findObject (n2);

	if (x1.value != x2.value) {
		return displayError (n1, ' Must Match');
	}
}

function validateLength (n, min, max) {
	var x;

	x = findObject (n);

	if (!validateMinimumLength (x.value, min)) {
		return displayError (n, ' is Too Short');
	} else if (!validateMaximumLength (x.value, max)) {
		return displayError (n, ' is Too Long');
	}
}

function validateEmpty (n) {
	var x;

	x = findObject (n);

	if (x.value == "") {
		return displayError (n, ' is Required');
	}
}

function validateEmail (n) {
	var x, p;

	x = findObject (n);
	p = x.indexOf ('@');
	if (p < 1 || p == (x.length - 1)) {
		return displayError (n, ' Must Contain an Email Address');
	}
}

function validateNumber (n, min, max) {
	var x, num;

	x = findObject (n);
	num = x.value;

	if (!validateDigits (num)) {
		return displayError (n, ' Must be a number');
	} else if (!validateRange (num, min, max)) {
		return displayError (n, ' Must be between ' + min + ' and ' + max);
	}
}

function validateNumeric (n, max1, max2) {
	var x, num1, num2;

	x = findObject (n + '_whole');
	num1 = x.value;

	x = findObject (n + '_decimal');
	num2 = x.value;

	if (!validateDigits (num1)) {
		return displayError (n, ' Whole portion must be a number');
	} else if (!validateMaximumLength (num1, max1)) {
		return displayError (n, ' Whole portion must be between 0 and ' + max1 + ' digits');
	} else if (!validateDigits (num2)) {
		return displayError (n, ' Decimal portion must be a number');
	} else if (!validateMaximumLength (num2, max2)) {
		return displayError (n, ' Decimal portion must be between 0 and ' + max2 + ' digits');
	}
}

function validateReal (n, precision, max) {
	var x, num1, num2;

	x = findObject (n + '_significant');
	num1 = x.value;

	x = findObject (n + '_exponent');
	num2 = x.value;

	if (!validateDigits (num1)) {
		return displayError (n, ' Significant portion must be a number');
	} else if (!validateMaximumLength (num1, precision)) {
		return displayError (n, ' Significant portion must be between 0 and ' + precision + ' digits');
	} else if (!validateDigits (num2)) {
		return displayError (n, ' Exponent portion must be a number');
	} else if (!validateRange (num2, 0, max)) {
		return displayError (n, ' Exponent portion must be between 0 and ' + max + ' digits');
	}
}

function validatePoint (n) {
	var x, num1, num2;

	x = findObject (n + '_x');
	num1 = x.value;

	x = findObject (n + '_y');
	num2 = x.value;

	if (!validateDigits (num1)) {
		return displayError (n, ' X-Coordinate must be a number');
	} else if (!validateDigits (num2)) {
		return displayError (n, ' Y-Coordinate must be a number');
	}
}

function validateCircle (n) {
	var x, num1, num2, num3;

	x = findObject (n + '_x');
	num1 = x.value;

	x = findObject (n + '_y');
	num2 = x.value;

	x = findObject (n + '_radius');
	num3 = x.value;

	if (!validateDigits (num1)) {
		return displayError (n, ' X-Coordinate must be a number');
	} else if (!validateDigits (num2)) {
		return displayError (n, ' Y-Coordinate must be a number');
	} else if (!validateDigits (num3)) {
		return displayError (n, ' Radius must be a number');
	}
}

function validateDate (n, c) {
	var x, year, month, day;

	if (c == 3) {
		x = findObject (n + '_year');
		year = x.value;

		x = findObject (n + '_month');
		month = x.value;

		x = findObject (n + '_day');
		day = x.value;

		if (!validateDigits (year)) {
			return displayError (n, ' Year must be a number');
		} else if (!validateDigits (month)) {
			return displayError (n, ' Month must be a number');
		} else if (!validateDigits (day)) {
			return displayError (n, ' Day must be a number');
		}
	}
}

function validateInet (n) {
	var x, num1, num2, num3, num4;

	x = findObject (n + '_octet1');
	num1 = x.value;

	x = findObject (n + '_octet2');
	num2 = x.value;

	x = findObject (n + '_octet3');
	num3 = x.value;

	x = findObject (n + '_octet4');
	num4 = x.value;

	if (!validateDigits (num1)) {
		return displayError (n, ' First octet must be a number');
	} else if (!validateRange (num1, 0, 255)) {
		return displayError (n, ' First octet must be between 0 and 255');
	} else if (!validateDigits (num2)) {
		return displayError (n, ' Second octet must be a number');
	} else if (!validateRange (num2, 0, 255)) {
		return displayError (n, ' Second octet must be between 0 and 255');
	} else if (!validateDigits (num3)) {
		return displayError (n, ' Third octet must be a number');
	} else if (!validateRange (num3, 0, 255)) {
		return displayError (n, ' Third octet must be between 0 and 255');
	} else if (!validateDigits (num4)) {
		return displayError (n, ' Fourth octet must be a number');
	} else if (!validateRange (num4, 0, 255)) {
		return displayError (n, ' Fourth octet must be between 0 and 255');
	}
}

function validateMacaddr (n) {
	var x, num1, num2, num3, num4, num5, num6;

	x = findObject (n + '_octet1');
	num1 = x.value;

	x = findObject (n + '_octet2');
	num2 = x.value;

	x = findObject (n + '_octet3');
	num3 = x.value;

	x = findObject (n + '_octet4');
	num4 = x.value;

	x = findObject (n + '_octet5');
	num5 = x.value;

	x = findObject (n + '_octet6');
	num6 = x.value;

	if (!validateHex (num1)) {
		return displayError (n, ' First octet must be hex');
	} else if (!validateHex (num2)) {
		return displayError (n, ' Second octet must be hex');
	} else if (!validateHex (num3)) {
		return displayError (n, ' Third octet must be hex');
	} else if (!validateHex (num4)) {
		return displayError (n, ' Fourth octet must be hex');
	} else if (!validateHex (num5)) {
		return displayError (n, ' Fifth octet must be hex');
	} else if (!validateHex (num6)) {
		return displayError (n, ' Sixth octet must be hex');
	}
}

function closeWindow () {
	self.close ();
}

function redirectParent (URL) {
	opener.parent.location=URL;
}

function reloadParent () {
	opener.parent.location.reload();
}

function redirectParentAndClose (URL) {
	redirectParent (URL);
	closeWindow ();
}

function reloadParentAndClose () {
	reloadParent ();
	closeWindow ();
}

function setParentValueAndClose (name, value) {
	var form_object = findParentObject (name);
	form_object.value = value;
	closeWindow ();
}

function setFormAction (name, action) {
	var form_object = findObject (name);
	form_object.action = action;
}

function submitForm (name) {
	var form_object = findObject (name);
	form_object.submit();
}

function roundNumber (value, places) {
	return Math.round (value * Math.pow (10, places)) / Math.pow (10, places);
}