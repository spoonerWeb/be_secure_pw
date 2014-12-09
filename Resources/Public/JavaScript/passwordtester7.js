/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/* Class to handle the password tester */

(function ($) {

	var _passwordStatus = 0;

	var _passwordSecure = false;

	function calculateStrength(passwordString) {
		if (beSecurePwConf.lowercaseChar == 1 && passwordString.search(/[a-z]/) != -1) {
			_passwordStatus++;
		}
		if (beSecurePwConf.capitalChar == 1 && passwordString.search(/[A-Z]/) != -1) {
			_passwordStatus++;
		}
		if (beSecurePwConf.digit == 1 && passwordString.search(/[0-9]/) != -1) {
			_passwordStatus++;
		}
		if (beSecurePwConf.specialChar == 1 && passwordString.search(/[^0-9a-z]/i) != -1) {
			_passwordStatus++;
		}
		if (_passwordStatus >= beSecurePwConf.patterns && passwordString.length >= beSecurePwConf.passwordLength) {
			_passwordSecure = true;
		}
	}

	$(document).ready(function() {
		$('#field_password').keyup(function() {
			_passwordStatus = 0;
			_passwordSecure = false;
			calculateStrength($(this).val());

			if (_passwordSecure) {
				$(this).parents('.form-group').addClass('has-success');
				$(this).parents('.form-group').removeClass('has-error');
				$('input.c-inputButton').prop('disabled', false);
			} else {
				$(this).parents('.form-group').addClass('has-error');
				$(this).parents('.form-group').removeClass('has-success');
				$('input.c-inputButton').prop('disabled', true);
			}
		});

		$('#field_password2').keyup(function() {
			if ($(this).val() === $('#field_password').val()) {
				$(this).parents('.form-group').addClass('has-success');
				$(this).parents('.form-group').removeClass('has-error');
			} else {
				$(this).parents('.form-group').addClass('has-error');
				$(this).parents('.form-group').removeClass('has-success');
			}
		});
	});
}(TYPO3.jQuery));