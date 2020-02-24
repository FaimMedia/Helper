<?php

namespace FaimMedia\Helper\Mail;

use FaimMedia\Helper\Mail\MailInterface;

use PHPMailer\PHPMailer\PHPMailer as PHPMailerClient;

use FaimMedia\Helper\Mail\Exception as MailException;

/**
 * Use PHPMailer sendmail implementation
 */
abstract class AbstractPHPMailer implements MailInterface {

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
	 * Set config abstract method
	 */
	abstract protected function setMailConfig(object $config);

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
	 * Clear receivers
	 */
	public function clearReceivers(): self {
		$this->getMail()->clearAllRecipients();

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
}