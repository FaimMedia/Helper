<?php

namespace FaimMedia\Helper;

use FaimMedia\Helper\Exception\ZipcodeException;

/**
 * Zipcode helper class for formatting zipcodes
 */
class Zipcode {

	/**
	 * Format zipcode according to zipcode format
	 *
	 * @format argument example:
	 * [
	 *     [
	 *         "character" : "-"
	 *         "position" : 4
	 *     ]
	 * ]
	 *
	 * Will format 123456 to 1234-56
	 */
	public static function format(string $zipcode = null, array $formats = null): string {
		$zipcode = self::parse($zipcode);

		if(!$formats) {
			return $zipcode;
		}

		$offset = 0;

		foreach($formats as $format) {
			if(!isset($format['character'])) {
				throw new ZipcodeException('Character field is not set', ZipcodeException::INVALID_CHARACTER);
			}

			if(!isset($format['position'])) {
				throw new ZipcodeException('Position field is not set', ZipcodeException::INVALID_POSITION);
			}

			$args = [
				$zipcode,
				(!empty($format['character'])) ? $format['character'] : ' ',
				$format['position'] + $offset,
				0,
			];

			$offset += strlen($format['character']);

			if(isset($format['length'])) {
				array_pop($args);

				$args[] = $format['length'];

				$offset -= $format['length'];
			}

			$zipcode = substr_replace(...$args);
		}

		return $zipcode;
	}

	/**
	 * Parse string to valid zipcode format
	 * Strip all non A-Z + 0-9 characters
	 */
	public static function parse(string $zipcode = null): ?string {
		$zipcode = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', $zipcode));

		if(empty($zipcode)) {
			return null;
		}

		return $zipcode;
	}
}