<?php

namespace FaimMedia\Helper\Mail;

use Exception as AbstractException;

/**
 * Mail Exception class
 */
class Exception extends AbstractException {

	const SEND_ERROR = -1;
	const ATTACHMENT_ERROR = -2;

	protected $_response;

	/**
	 * Change exception message
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * Set mail response
	 */
	public function setResponse($response): self {
		$this->_response = $response;

		return $this;
	}

	/**
	 * Get response
	 */
	public function getResponse() {
		return $this->_response;
	}
}