<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

use felicity\config\Config;

$fileName = basename($_SERVER['SCRIPT_FILENAME'], '.php');

Config::set('lang.translations.en.felicityCore', [
    'followingExceptionCaught' => 'The following exception was caught',
    'getTrace' => 'To get the backtrace, use the option: --trace=true',
    'felicityCommandLine' => 'Felicity Command Line',
    'usage:' => 'Usage:',
    'usageExample' => "./{$fileName} [command/path] [--arg=val] [--arg2=val]",
    'noCliCommands' => 'No CLI commands have been defined',
    'commandNotFound' => 'Command not found'
]);
