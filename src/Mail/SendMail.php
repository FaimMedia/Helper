<?php

namespace FaimMedia\Helper\Mail;

use FaimMedia\Helper\Mail\MailInterface;

use PHPMailer\PHPMailer\PHPMailer as PHPMailerClient;

use FaimMedia\Helper\Mail\Exception as MailException;

/**
 * Use SendMail implementation of PHPMailer
 */
class SendMail extends AbstractPHPMailer implements MailInterface {

	/**
	 * Get type
	 */
	public function getType(): int {
		return self::TYPE_SENDMAIL;
	}

	/**
	 * Set config
	 */
	protected function setMailConfig(object $config) {

		$mail = $this->getMail();
		$mail->CharSet = 'utf-8';

	// set fields
		$fields = ['from', 'fromName'];
		foreach($fields as $field) {
			if(!isset($config->$field)) {
				continue;
			}

			$$field = $config->$field;
		}

		if(isset($fromName)) {
			$mail->FromName = $fromName;
		}

		if(isset($from)) {
			$mail->From = $from;
		}

		$mail->isSendmail();
	}
}