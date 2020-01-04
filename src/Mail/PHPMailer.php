<?php

namespace FaimMedia\Helper\Mail;

use FaimMedia\Helper\Mail\MailInterface;

use PHPMailer\PHPMailer\PHPMailer as PHPMailerClient,
    PHPMailer\PHPMailer\SMTP;

use FaimMedia\Helper\Mail\Exception as MailException;

class PHPMailer implements MailInterface {

	protected $_mail;
	protected $_debug = false;

	/**
	 * Constructor
	 */
	public function __construct(object $config) {
		$this->setMail(new PHPMailerClient());
		$this->setMailConfig($config);
	}

	/**
	 * Get type
	 */
	public function getType(): int {
		return self::TYPE_SMTP;
	}

	/**
	 * Set the mail object
	 */
	protected function setMail($mail): self {
		$this->_mail = $mail;

		return $this;
	}

	/**
	 * Get the mail object
	 */
	protected function getMail() {
		return $this->_mail;
	}

	/**
	 * Set config
	 */
	protected function setMailConfig(object $config) {
		$port = 587;
		$sendmail = false;

		$fields = ['sendmail', 'host', 'protocol', 'port', 'username', 'password', 'from', 'fromName'];
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
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;
		}

		$mail->CharSet = 'utf-8';

		if(!$sendmail) {
			$mail->Host = $host;
			$mail->Port = $port;
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = $protocol;

			$mail->Username = $username;
			$mail->Password = $password;

			$mail->isSMTP();
		} else {
			$mail->isSendmail();
		}
	}

	/**
	 * Set sender
	 */
	public function setFrom(string $from, string $fromName = null): self {
		if($fromName) {
			$this->getMail()->setFrom($from, $fromName);
		} else {
			$this->getMail()->setFrom($from);
		}

		return $this;
	}

	/**
	 * Set reply to
	 */
	public function setReplyTo(string $replyTo, string $replyToName = null): self {
		$this->getMail()->ReturnPath = $replyTo;

		if($replyToName) {
			$replyToName .= ' ';
		}

		$this->getMail()->addCustomHeader('In-Reply-To', $replyToName.' <' . $replyTo . '>');

		return $this;
	}

	/**
	 * Set To
	 */
	public function addReceiver(string $address, $name = null): self {
		$this->getMail()->addAddress($address, $name);

		return $this;
	}

	/**
	 * Set Cc
	 */
	public function addCc(string $address, $name = null): self {
		$this->getMail()->addCc($address, $name);

		return $this;
	}

	/**
	 * Set Cc
	 */
	public function addBcc(string $address, $name = null): self {
		$this->getMail()->addBcc($address, $name);

		return $this;
	}

	/**
	 * Set subject
	 */
	public function setSubject(string $subject = null): self {
		$this->getMail()->Subject = ucfirst($subject);

		return $this;
	}

	/**
	 * Set mail body
	 */
	public function setBody(string $body = null): self {
		$this->getMail()->isHTML(true);
		$this->getMail()->Body = $body;

		return $this;
	}

	/**
	 * Add attachment
	 */
	public function addAttachment(string $path, string $name = null): self {
		$this->getMail()->addAttachment($path, $name);

		return $this;
	}

	/**
	 * Add inline attachment
	 */
	public function addInlineAttachment(string $path, string $cid, string $name = null): self {
		$this->getMail()->addEmbeddedImage($path, $cid, $name);

		return $this;
	}

	/**
	 * Send mail
	 */
	public function send(): bool {
		if(!$this->getMail()->send()) {
			if($this->_debug) {
				throw new MailException($this->getMail()->ErrorInfo);
			}

			error_log('PHPMailer send error: '.$this->getMail()->ErrorInfo);
			return false;
		}

		return true;
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