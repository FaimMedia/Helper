<?php

namespace FaimMedia\Helper\Mail;

use FaimMedia\Helper\Mail\MailInterface;

use PHPMailer\PHPMailer\PHPMailer as PHPMailerClient,
    PHPMailer\PHPMailer\SMTP as PHPMailerSMTP;

use FaimMedia\Helper\Mail\Exception as MailException;

/**
 * Use SMTP implementation of PHPMailer
 */
class SMTP extends AbstractPHPMailer implements MailInterface {

	/**
	 * Get type
	 */
	public function getType(): int {
		return self::TYPE_SMTP;
	}

	/**
	 * Set config
	 */
	protected function setMailConfig(object $config) {
		$port = 587;

		$fields = ['host', 'protocol', 'port', 'username', 'password', 'from', 'fromName'];
		foreach($fields as $field) {
			if(!isset($config->$field)) {
				continue;
			}

			$$field = $config->$field;
		}

		if(isset($config['_debug'])) {
			$this->_debug = $config['_debug'];
		}

		$mail = $this->getMail();

		if($this->_debug) {
			$mail->SMTPDebug = PHPMailerSMTP::DEBUG_SERVER;
		}

		$mail->CharSet = 'utf-8';

		$mail->Host = $host;
		$mail->Port = $port;
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = $protocol;

		$mail->Username = $username;
		$mail->Password = $password;

		$mail->isSMTP();
	}

	/**
	 * Close SMTP connection
	 */
	public function __destruct() {
		if($this->getMail()->SMTPKeepAlive) {
			$this->getMail()->SmtpClose();
		}
	}
}