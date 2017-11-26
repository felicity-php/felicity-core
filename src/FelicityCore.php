<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core;

use ReflectionException;
use voku\helper\AntiXSS;
use felicity\config\Config;
use felicity\core\models\UriModel;
use felicity\core\services\config\Routing;
use felicity\core\services\request\UriService;
use felicity\core\services\request\RoutingService;

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
            Config::getInstance(),
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
        return new RoutingService(Routing::getInstance());
    }
}
