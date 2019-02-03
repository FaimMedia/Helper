<?php

namespace FaimMedia\Helper\Cli;

class Color {

	protected static $_isCli;

	/**
	 * Check CLI mode
	 */
	protected static function isCli(): bool {
		if(self::$_isCli === null) {
			self::$_isCli = (php_sapi_name() == 'cli');
		}

		return self::$_isCli;
	}

	/**
	 * Returns a parsed colored string for CLI output
	 */
	public static function parse(string $string, string $foregroundColor = null, string $backgroundColor = null, bool $bold = false): string {
	// Set up shell colors
		$colors = [
			'black'  => 30,
			'red'    => 31,
			'green'  => 32,
			'yellow' => 33,
			'blue'   => 34,
			'purple' => 35,
			'cyan'   => 36,
			'white'  => 37,
		];

		$returnString = "";

	// if not in CLI mode, ignore colors and output string directly
		if(!self::isCli()) {
			return $string;
		}

	// Check if given foreground color found
		if(isset($colors[$foregroundColor])) {
			$returnString .= "\033[" . ($bold ? '1' : '0') . ';' . $colors[$foregroundColor] . "m";
		}

	// Check if given background color found
		if(isset($colors[$backgroundColor])) {
			$returnString .= "\033[" . ($colors[$backgroundColor] + 10) . "m";
		}

		$returnString .=  $string . "\033[0m";

		return $returnString;
	}
}