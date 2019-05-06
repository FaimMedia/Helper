<?php

namespace FaimMedia\Helper;

use FaimMedia\Helper\Mail\MailInterface;

use FaimMedia\Helper\Mail\Exception as MailException;

/**
 * Mail wrapper class
 */
class Mail {

	protected $_mail;

	protected static $_mailInstance;

	/**
	 * Constructor
	 */
	public function __construct(MailInterface $mail = null) {

		if($mail !== null) {
			$this->setMail($mail);
		}

		$this->getMail();
	}

	/**
	 * Set mail instance
	 */
	protected function setMail(MailInterface $mail): self {
		$this->_mail = $mail;

		return $this;
	}

	/**
	 * Get (clone) mail instance
	 */
	public function getMail(): MailInterface {
		if(!$this->_mail) {
			$this->setMail($this->getInstance());
		}

		return $this->_mail;
	}

	/**
	 * Get mail instance
	 * 	Clone object, so configuration stays
	 */
	protected function getInstance(): MailInterface {
		return clone self::$_mailInstance;
	}

	/**
	 * Magic caller to forward to mail instance
	 */
	public function __call($name, $arguments) {
		if(is_callable([$this->getMail(), $name])) {
			return call_user_func_array([$this->getMail(), $name], $arguments);
		}

		throw new MailException('Method `'.$name.'` does not exist on `'.static::class.'` class');
	}

/**
 * STATIC
 */
	/**
	 * Setup
	 */
	public static function setup(MailInterface $mail) {
		self::$_mailInstance = $mail;
	}
}
