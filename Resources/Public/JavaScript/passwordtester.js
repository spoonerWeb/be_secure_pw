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

var PasswordTester;
PasswordTester = Class.create({
	passwordStatus: 0,
	passwordField: null,
	saveButton: null,
	colorRGB: 'white',
	message: '',

	initialize: function () {
		Ext.onReady(function () {
			this.passwordField = Ext.get('field_password');

			this.saveButton = Ext.select('input[name="data[save]"]');

			this.passwordSecure = false;

			this.passwordField.on('keyup', function () {
				var password = this.passwordField.getValue();

				// set status to zero on each keyup
				this.passwordStatus = 0;
				this.passwordSecure = false;

				// calculates the strength of the password
				this.calculateStrength(password);

				// generates the field with the image
				this.generateImage();

				if (password.length == 0) {
					this.saveButton.set({
						disabled: null
					}, false);
					Ext.destroy(Ext.get('password_strength'));
				}

			}, this);
		}, this);
	},

	calculateStrength: function (passwordString) {
		if (beSecurePwConf.lowercaseChar == 1 && passwordString.search(/[a-z]/) != -1) {
			this.passwordStatus++;
		}
		if (beSecurePwConf.capitalChar == 1 && passwordString.search(/[A-Z]/) != -1) {
			this.passwordStatus++;
		}
		if (beSecurePwConf.digit == 1 && passwordString.search(/[0-9]/) != -1) {
			this.passwordStatus++;
		}
		if (beSecurePwConf.specialChar == 1 && passwordString.search(/[^0-9a-z]/i) != -1) {
			this.passwordStatus++;
		}
		if (this.passwordStatus >= beSecurePwConf.patterns && passwordString.length >= beSecurePwConf.passwordLength) {
			this.passwordSecure = true;
		}
	},

	generateImage: function () {
		Ext.destroy(Ext.get('password_strength'));
		this.passwordField.setStyle('float', 'left');
		if (this.passwordSecure == true) {
			Ext.DomHelper.insertAfter(
				this.passwordField,
				{
					tag: 'img',
					src: '../../../../typo3conf/ext/be_secure_pw/Resources/Public/Images/accept.png',
					id: 'password_strength',
					style: 'margin: 2px 0 0 5px;'
				},
				false
			);
			this.saveButton.set({
				disabled: false
			}, false);
		} else {
			Ext.DomHelper.insertAfter(
				this.passwordField,
				{
					tag: 'img',
					src: '../../../../typo3/sysext/t3skin/icons/gfx/required_h.gif',
					id: 'password_strength',
					style: 'margin: 2px 0 0 5px;'
				},
				false
			);
			this.saveButton.set({
				disabled: 'disabled'
			});
		}
	}
});

var TYPO3BackendPasswordTester = new PasswordTester();