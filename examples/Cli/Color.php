#!/usr/bin/env php
<?php

/**
 * Example file for parsing CLI colored output
 */

use FaimMedia\Helper\Cli\Color;

require dirname(__DIR__, 5) . '/vendor/autoload.php';

echo Color::parse('This text is red colored', 'red');
echo Color::parse('This background is red colored', null, 'red');
echo Color::parse('This text is bold', null, null, true);