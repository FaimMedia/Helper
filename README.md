# FaimMedia Helper library

Helper library with useful scripts.

## Install

Install by using composer:

    composer require faimmedia/helper

## Usage

You may find example files inside the `examples` folder.

## Documentation

### Cli

Scripts for using in CLI mode.

#### Cli\Helper

`parse` (`string` `$text`, ?`string` `$foregroundColor`, ?`string` `$backgroundColor`, `bool` `$bold` = `false`)

Parse string to colored output.

Accepted colors for `$foregroundColor` and `$backgroundColor` arguments:

* `black`
* `red`
* `green`
* `yellow`
* `blue`
* `purple`
* `cyan`
* `white`

