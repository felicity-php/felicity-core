<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\twig;

use felicity\core\exceptions\HttpException;

/**
 * Class ThrowTwigFunction
 */
class ThrowErrorTwigFunction
{
    /**
     * Add the twig function
     * @throws HttpException
     */
    public function add()
    {
        if (! class_exists('\felicity\twig\Twig')) {
            return;
        }

        \felicity\twig\Twig::get()->addFunction(
            new \Twig_function('throwError', function ($code = 404, $msg = '') {
                throw new HttpException($msg, $code);
            })
        );
    }
}