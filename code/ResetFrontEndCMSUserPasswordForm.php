<?php
/**
 * Log-in form for the "member" authentication method
 * @package sapphire
 * @subpackage security
 */
class ResetFrontEndCMSUserPasswordForm extends MemberLoginForm {

	/**
	 * Get message from session
	 */
	protected function getMessageFromSession() {
		parent::getMessageFromSession();
		$this->message = '';
	}

}
