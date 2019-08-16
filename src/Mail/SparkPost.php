<?php

namespace FaimMedia\Helper\Mail;

use FaimMedia\Helper\Mail\MailInterface;

use FaimMedia\Helper\Mail\Exception as MailException;

/**
 * Sparkpost instance, use to send mail through Sparkpost API
 */
class SparkPost implements MailInterface {

	const API_URL = 'https://api.eu.sparkpost.com/api/v1/';

	protected $_config;

	protected $_from;
	protected $_fromName;
	protected $_replyTo;
	protected $_replyToName;

	protected $_receivers = [];
	protected $_cc = [];
	protected $_bcc = [];

	protected $_subject;
	protected $_body;

	protected $_attachments = [];
	protected $_inlineAttachments = [];

	/**
	 * Constructor
	 */
	public function __construct(object $config) {
		$this->setMailConfig($config);
	}

	/**
	 * Get type
	 */
	public function getType(): int {
		return self::TYPE_SPF;
	}

	/**
	 * Set config
	 */
	protected function setMailConfig(object $config): self {
		$this->_config = $config;

		if($config->from) {
			if($config->fromName) {
				$this->setFrom($config->from, $config->fromName);
			} else {
				$this->setFrom($config->from);
			}
		}

		return $this;
	}

	/**
	 * Get config
	 */
	protected function getMailConfig(): object {
		return $this->_config;
	}

	/**
	 * Set sender
	 */
	public function setFrom(string $from, string $fromName = null): self {
		$this->_from = $from;
		$this->_fromName = $fromName;

		return $this;
	}

	/**
	 * Set reply to
	 */
	public function setReplyTo(string $replyTo, string $replyToName = null): self {
		$this->_replyTo = $replyTo;
		$this->_replyToName = $replyToName;

		return $this;
	}

	/**
	 * Set To
	 */
	public function addReceiver(string $address, $name = null): self {
		$this->_receivers[$address] = $name;

		return $this;
	}

	/**
	 * Set Cc
	 */
	public function addCc(string $address, $name = null): self {
		$this->_cc[$address] = $name;

		return $this;
	}

	/**
	 * Set Cc
	 */
	public function addBcc(string $address, $name = null): self {
		$this->_bcc[$address] = $name;

		return $this;
	}

	/**
	 * Set subject
	 */
	public function setSubject(string $subject = null): self {
		$this->_subject = $subject;

		return $this;
	}

	/**
	 * Set mail body
	 */
	public function setBody(string $body = null): self {
		$this->_body = $body;

		return $this;
	}

	/**
	 * Add attachment
	 */
	public function addAttachment(string $path, string $name = null): self {
		$this->_attachments[] = $this->parseFile($path, $name);

		return $this;
	}

	/**
	 * Add inline attachment
	 */
	public function addInlineAttachment(string $path, string $cid, string $name = null): self {
		$this->_inlineAttachments[] = $this->parseFile($path, $cid);

		return $this;
	}

	/**
	 * Send mail
	 */
	public function send(): bool {

		$curl = $this->buildCurl();

		$response = curl_exec($curl);

		$json = json_decode($response);

		$statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);

		//curl_close($curl);

		if((int)substr($statusCode, 0, 1) !== 2) {

			$exception = new MailException('Could not send mail', MailException::SEND_ERROR);
			$exception->setResponse($response);

			if(isset($json->errors) && count($json->errors) > 0) {
				$firstError = array_shift($json->errors);

				$message = $firstError->message;
				if(isset($firstError->description)) {
					$message .= ': '.$firstError->description;
				}

				$exception->setMessage($message);
			}

			throw $exception;
		}

		return true;
	}

	/**
	 * Build send CURL
	 */
	protected function buildCurl() {

		$apiKey = $this->getMailConfig()->apiKey;
		if(empty($apiKey)) {
			throw new MailException('Invalid SparkPost EU API key is provided, please verify');
		}

	// set json
		$json = json_encode($this->getPayload(), JSON_PRETTY_PRINT);

	// set curl
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL            => self::API_URL.'transmissions/',
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_POSTFIELDS     => $json,
			CURLOPT_HTTPHEADER     => [
				'Authorization: '.$apiKey,
				'Content-Type: application/json',
				'Content-Length: '.strlen($json),
			],
			CURLOPT_RETURNTRANSFER => 1
		]);

		return $curl;
	}

	/**
	 * Build payload
	 */
	protected function getPayload(): array {
		$recipients = [];

	// set content
		$content = [
			'headers' => [],
			'from'    => [
				'name'  => $this->getFromName(),
				'email' => $this->getFrom(),
			],
			'subject' => $this->getSubject(),
			'text'    => null,
			'html'    => $this->getBody(),

			'attachments'   => [],
			'inline_images' => [],
		];

		if($this->getReplyTo()) {
			$content['reply_to'] = trim($this->getReplyToName()).' <'.$this->getReplyTo().'>';
		}

	// set attachments
		foreach($this->getAttachments() as $attachment) {
			$content['attachments'][] = $attachment;
		}

		foreach($this->getInlineAttachments() as $inlineAttachment) {
			$content['inline_images'][] = $inlineAttachment;
		}

	// set receivers
		$receivers = $this->getReceivers();
		foreach($receivers as $address => $name) {
			$recipients[] = [
				'address' => [
					'email' => $address,
					'name'  => $name,
				],
			];
		}

	// get first receiver
		reset($receivers);
		$firstReceiver = key($receivers);

	// add CC + BCC
		foreach(array_merge($this->getCcs(), $this->getBccs()) as $address => $name) {
			$recipients[] = [
				'address' => [
					'email'     => $address,
					'name'      => $name,
					'header_to' => $firstReceiver,
				],
			];
		}

	// Check CCs and set header accordingly
		$ccs = $this->getCcs();
		if($ccs) {
			$content['headers']['CC'] = join(', ', array_keys($ccs));
		}

	// unset empty array, will cause invalid type error
		foreach(['headers', 'attachments', 'inline_images'] as $type) {
			if(!empty($content[$type])) {
				continue;
			}

			unset($content[$type]);
		}

		return [
			'recipients' => $recipients,
			'content'    => $content,
			'options'    => [

			// @todo: build option for this
				'open_tracking'  => false,
				'click_tracking' => false,
			],
		];
	}

	/**
	 * Parse file to SparkPost Base64 data array
	 */
	protected function parseFile(string $file, string $name = null): array {
		if(!file_exists($file)) {
			throw new MailException('The specified file `'.$file.'` does not exist', MailException::ATTACHMENT_ERROR);
		}

		$mime = mime_content_type($file);
		if(!$mime) {
			throw new MailException('Could not determain MIME-type', MailException::ATTACHMENT_ERROR);
		}

		$data = file_get_contents($file);

		if(empty($name)) {
			$name = basename($file);
		}

		return [
			'name' => $name,
			'type' => $mime,
			'data' => base64_encode($data),
		];
	}

/* GETTERS */

	public function getFrom(): string {
		return $this->_from;
	}

	public function getFromName(): ?string {
		return $this->_fromName;
	}

	public function getReplyTo(): ?string {
		return $this->_replyTo;
	}

	public function getReplyToName(): ?string {
		return $this->_replyToName;
	}

	public function getReceivers(): array {
		return $this->_receivers;
	}

	public function getCcs(): array {
		return $this->_cc;
	}

	public function getBccs(): array {
		return $this->_bcc;
	}

	public function getSubject(): ?string {
		return $this->_subject;
	}

	public function getBody(): ?string {
		return $this->_body;
	}

	public function getAttachments(): array {
		return $this->_attachments;
	}

	public function getInlineAttachments(): array {
		return $this->_inlineAttachments;
	}
}