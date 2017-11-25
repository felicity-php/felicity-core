<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\services\config;

/**
 * Class Config
 */
class Config
{
    /** @var Config $instance */
    public static $instance;

    /** @var array $configItems */
    private $configItems = [];

    /**
     * Bootstraps the config class instance
     */
    public function bootstrap()
    {
        self::getInstance();
    }

    /**
     * Gets the config class instance
     * @return Config Singleton
     */
    public static function getInstance() : Config
    {
        if (! self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Sets config item
     * @param string $key
     * @param mixed $val
     * @return Config
     */
    public function set(string $key, $val) : Config
    {
        $this->setArrayDot($this->configItems, $key, $val);
        return $this;
    }

    /**
     * A static method to set a config item
     * @param string $key
     * @param mixed $val
     * @return Config
     */
    public static function setItem(string $key, $val) : Config
    {
        return self::getInstance()->set($key, $val);
    }

    /**
     * Gets a config item
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->getArrayDot($this->configItems, $key) ?: $default;
    }

    /**
     * A static method to get a config item
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getItem(string $key, $default = null)
    {
        return self::getInstance()->get($key, $default);
    }

    /**
     * Sets an array with dot syntax
     * @param array $arr
     * @param string $path
     * @param mixed $val
     */
    private function setArrayDot(array &$arr, string $path, $val)
    {
        $loc = &$arr;
        foreach (explode('.', $path) as $step) {
            $loc = &$loc[$step];
        }
        $loc = $val;
    }

    /**
     * Gets an array item from dot syntax
     * @param array $arr
     * @param string $path
     * @return mixed
     */
    private function getArrayDot(array $arr, string $path)
    {
        $val = $arr;

        foreach (explode('.', $path) as $step) {
            if (! isset($val[$step])) {
                return null;
            }

            $val = $val[$step];
        }

        return $val;
    }
}
