#!/usr/bin/env php
<?php

/**
 * Example file for parsing CLI colored output
 */

use FaimMedia\Helper\Cli\Color;

require dirname(__DIR__, 5) . '/vendor/autoload.php';

// colors
	$colors = [
		'black',
		'red',
		'green',
		'yellow',
		'blue',
		'purple',
		'cyan',
		'white',
	];

// output
	foreach($colors as $color) {
		echo Color::parse('This text is '.$color.' colored', $color);
		echo PHP_EOL;
		echo Color::parse('This background is '.$color.' colored', null, $color);
		echo PHP_EOL;
		echo Color::parse('This text is '.$color.' colored and bold', $color, null, true);
		echo PHP_EOL;
	}