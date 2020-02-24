<?php

namespace FaimMedia\Helper\Mail;

/**
 * Mail class Interface
 */
interface MailInterface {

	const TYPE_SMTP = 1;
	const TYPE_SPF = 2;
	const TYPE_SENDMAIL = 3;

	public function __construct(object $config);

	/**
	 * Method to check the library type
	 */
	public function getType(): int;

	/**
	 * Set sender
	 */
	public function setFrom(string $from, string $fromName = null);

	/**
	 * Set reply to
	 */
	public function setReplyTo(string $from);

	/**
	 * Set mail receivers
	 */
	public function addReceiver(string $address, $name = null);

	/**
	 * Clear receivers
	 */
	public function clearReceivers();

	/**
	 * Set mail CC receivers
	 */
	public function addCc(string $address, $name = null);

	/**
	 * Set mail BCC receivers
	 */
	public function addBcc(string $address, $name = null);

	/**
	 * Set the mail subject
	 */
	public function setSubject(string $subject = null);

	/**
	 * Add email attachment
	 */
	public function addAttachment(string $path, string $name = null);

	/**
	 * Add inline attachment
	 */
	public function addInlineAttachment(string $path, string $cid, string $name = null);

	/**
	 * Set the mail body
	 */
	public function setBody(string $text = null);

	/**
	 * Send the generated email
	 */
	public function send(): bool;

}
