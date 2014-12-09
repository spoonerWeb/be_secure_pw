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

/**
 * Password reminder window appearing in the backend
 */

(function ($) {
	$(document).ready(function() {
		TYPO3.Dialog.InformationDialog({
			title: TYPO3.LLL.beSecurePw.passwordReminderWindow_title,
			msg: TYPO3.LLL.beSecurePw.passwordReminderWindow_message,
			url: $(this).data('href'),
			fn: function(button, dummy, dialog) {
				if (button == 'ok') {
					top.goToModule('user_setup');
				}
			}
		});
	});
}(TYPO3.jQuery));