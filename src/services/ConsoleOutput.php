<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\services;

/**
 * Class ConsoleOutput
 */
class ConsoleOutput
{
    /**
     * Writes a line to the console
     * @param string $line
     * @param string $color
     * @param bool $addBreak
     */
    public static function write(
        $line,
        $color = '',
        bool $addBreak = true
    ) {
        // Formats
        $reset = $cColor = "\033[0m";
        $red = "\033[31m";
        $green = "\033[32m";
        $yellow = "\033[33m";
        $bold = "\033[1m";

        // Determine if color is something we can deal with
        if ($color === 'red') {
            $cColor = $red;
        } elseif ($color === 'green') {
            $cColor = $green;
        } elseif ($color === 'yellow') {
            $cColor = $yellow;
        }

        $line = strtr($line, array(
            '<red>' => $red,
            '</red>' => $reset,
            '<green>' => $green,
            '</green>' => $reset,
            '<yellow>' => $yellow,
            '</yellow>' => $reset,
            '<bold>' => $bold,
            '</bold>' => $reset,
        ));

        echo "{$cColor}{$line}{$reset}";

        if ($addBreak) {
            echo "\n";
        }
    }

    /**
     * Writes a line to the console
     * @param string $line
     * @param string $color
     * @param bool $addBreak
     */
    public function writeLn(
        string $line,
        string $color = '',
        bool $addBreak = true
    ) {
        self::write($line, $color, $addBreak);
    }
}
