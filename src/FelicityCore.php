<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core;

use voku\helper\AntiXSS;
use felicity\core\services\config\Config;
use felicity\core\services\config\Routing;
use felicity\core\services\request\UriService;

/**
 * Class FelicityCore
 */
class FelicityCore
{
    /**
     * Gets the config instance
     * @return Config
     */
    public static function getConfig() : Config
    {
        return Config::getInstance();
    }

    /**
     * Gets the routing instance
     * @return Routing
     */
    public static function getRouting() : Routing
    {
        return Routing::getInstance();
    }

    /** @var $uriModel */
    private static $uriModel;

    /**
     * Get's the URI model
     */
    public static function getUriModel()
    {
        if (self::$uriModel !== null) {
            return self::$uriModel;
        }

        $uriService = new UriService(
            self::getConfig(),
            new AntiXSS()
        );

        self::$uriModel = $uriService->getUriModel($_SERVER['REQUEST_URI']);

        return self::$uriModel;
    }
}
