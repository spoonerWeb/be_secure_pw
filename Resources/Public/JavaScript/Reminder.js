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
 * Module: SpoonerWeb/BeSecurePw/Reminder
 * JavaScript to handle password reminder modal
 */
define( [ 'jquery', 'TYPO3/CMS/Backend/Modal' ], function ( $, Modal ) {
	'use strict';

	$( function () {
		Modal.confirm( TYPO3.LLL.beSecurePw.passwordReminderWindow_title, TYPO3.LLL.beSecurePw.passwordReminderWindow_message + " " + TYPO3.LLL.beSecurePw.passwordReminderWindow_confirmation)
			.on( 'confirm.button.ok', function () {
				Modal.currentModal.trigger( 'modal-dismiss' );
				top.goToModule( 'user_setup' );
			} )
			.on( 'confirm.button.cancel', function () {
				Modal.currentModal.trigger( 'modal-dismiss' );
			} );
	} );
} );