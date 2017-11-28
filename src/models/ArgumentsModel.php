<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\models;

use felicity\datamodel\Model;

/**
 * Class ArgumentsModel
 */
class ArgumentsModel extends Model
{
    /** @var array $rawArguments */
    public $rawArguments = [];

    /** @var string $route */
    public $route = '';

    /** @var array $arguments */
    public $arguments = [];

    /**
     * Add raw args array
     * @param array $rawArgs
     * @return self
     */
    public function addRawArgs(array $rawArgs) : self
    {
        $this->rawArguments = $args = $rawArgs;

        if (! isset($args[0])) {
            return $this;
        }

        unset($args[0]);

        $args = array_values($args);

        $parsedArgs = [];

        foreach ($args as $key => $rawArg) {
            if (strpos($rawArg, '--') !== 0) {
                continue;
            }

            $rawArg = explode('--', $rawArg);
            unset($rawArg[0]);
            $rawArg = $rawArg[1];

            $rawArg = explode('=', $rawArg);

            $parsedArgs[$rawArg[0]] = $rawArg[1] ?? null;

            unset($args[$key]);
        }

        $this->arguments = $parsedArgs;

        if (! isset($args[0])) {
            return $this;
        }

        $this->route = $args[0];

        return $this;
    }

    /**
     * Gets an argument from the arguments array
     * @param string $key
     * @return mixed|null
     */
    public function getArgument(string $key)
    {
        if (! isset($this->arguments[$key])) {
            return null;
        }

        return $this->arguments[$key];
    }
}
