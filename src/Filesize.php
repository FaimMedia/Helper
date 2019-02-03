<?php

namespace FaimMedia\Helper;

/**
 * Filesize convert class
 */
class Filesize {

	/**
	 * Convert filesize as saved in PHP ini file to bytes
	 */
	public static function convertIniSizeToBytes($sSize): int {
		if(is_numeric($sSize)) {
		   return $sSize;
		}

		$sSuffix = substr($sSize, -1);
		$iValue = substr($sSize, 0, -1);
		switch(strtoupper($sSuffix)) {
			case 'P':
				$iValue *= 1024;
			case 'T':
				$iValue *= 1024;
			case 'G':
				$iValue *= 1024;
			case 'M':
				$iValue *= 1024;
			case 'K':
				$iValue *= 1024;
				break;
		}

		return $iValue;
	}

	/**
	 * Convert filesize in bytes to human readable file size
	 */
	public function toFileSize($bytes, $decimals = 2, $byte = 1000): string {
		$size = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
		$factor = floor((strlen($bytes) - 1) / 3);

		$locale = localeconv();

		$decimal = $locale['decimal_point'];
		$thousand = $locale['thousands_sep'];

		$format = (float)sprintf("%.{$decimals}f", $bytes / pow($byte, $factor));

		return number_format($format, $decimals, $decimal, $thousand) . ' ' . @$size[$factor];
	}
}