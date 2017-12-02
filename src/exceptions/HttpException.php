<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\exceptions;

use Exception;
use Throwable;
use felicity\config\Config;
use felicity\logging\Logger;
use felicity\core\FelicityCore;

/**
 * Class HttpException
 */
class HttpException extends Exception
{
    /**
     * HttpException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Renders the error message
     * @throws self
     */
    public function render()
    {
        Logger::log(
            'HttpException render starting, cleaning output buffer...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        ob_clean();

        ob_start();

        http_response_code($this->getCode());

        $showErrors = Config::get('showErrors');

        if ($showErrors && $this->getCode() !== 404) {
            Logger::log(
                'Config is asking for full error, throwing full error...',
                Logger::LEVEL_INFO,
                'felicityCore'
            );

            throw $this;
        }

        $customErrorMethod = Config::get("customErrorMethod{$this->getCode()}");

        if (\is_callable($customErrorMethod)) {
            Logger::log(
                'Config has custom error method for code ' . $this->getCode() .
                    ', calling custom method...',
                Logger::LEVEL_INFO,
                'felicityCore'
            );

            echo($customErrorMethod($this));

            ob_end_flush();

            return;
        }

        $customErrorMethod = Config::get('customErrorMethod');

        if (\is_callable($customErrorMethod)) {
            Logger::log(
                'Config has custom error method, calling custom method...',
                Logger::LEVEL_INFO,
                'felicityCore'
            );

            echo($customErrorMethod($this));

            ob_end_flush();

            return;
        }

        Logger::log(
            'Rendering internal HttpException template...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        include FelicityCore::getCoreSrcDir() . '/templates/HttpException.php';

        ob_end_flush();
    }
}
