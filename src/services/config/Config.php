<?php

namespace felicity\core\services\config;

/**
 * Class Config
 */
class Config
{
    /** @var Config $instance */
    public static $instance;

    /** @var array $config */
    private $configItems = [];

    /**
     * Bootstraps the config class
     */
    public function bootstrap()
    {
        self::$instance = $this;
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
     * Gets a config item
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->getArrayDot($this->configItems, $key);
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
