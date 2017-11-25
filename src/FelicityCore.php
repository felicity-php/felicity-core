<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core;

use felicity\core\models\UriModel;
use felicity\core\services\request\RoutingService;
use ReflectionException;
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
     * Get's the core src dir
     * @return string
     */
    public static function getCoreSrcDir() : string
    {
        return __DIR__;
    }

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
     * @return UriModel
     * @throws ReflectionException
     */
    public static function getUriModel() : UriModel
    {
        if (self::$uriModel !== null) {
            return self::$uriModel;
        }

        $uriService = new UriService(
            self::getConfig(),
            new AntiXSS()
        );

        self::$uriModel = $uriService->getUriModel(
            $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD']
        );

        return self::$uriModel;
    }

    /**
     * Gets the RoutingService
     * @return RoutingService
     */
    public static function getRoutingService() : RoutingService
    {
        return new RoutingService(self::getRouting());
    }
}
