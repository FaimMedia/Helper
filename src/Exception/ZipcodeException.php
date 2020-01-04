<?php

namespace FaimMedia\Helper\Exception;

use Exception;

/**
 * Zipcode Exception class
 */
class ZipcodeException extends Exception {
	const INVALID_CHARACTER = -1;
	const INVALID_POSITION = -2;
}