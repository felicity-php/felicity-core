<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\services\config;

use ReflectionException;
use felicity\datamodel\ModelCollection;
use felicity\core\models\MatchedRouteModel;

/**
 * Class Config
 */
class Routing
{
    /** @var Config $instance */
    public static $instance;

    /** @var array $routes */
    private $routes = [
        // 'get' => [],
        // 'post' => [],
        // 'put' => [],
        // 'patch' => [],
        // 'delete' => [],
        // 'options' => [],
        // 'cli' => []
    ];

    /**
     * Bootstraps the routing class instance
     */
    public function bootstrap()
    {
        self::getInstance();
    }

    /**
     * Gets the config class instance
     * @return Routing Singleton
     */
    public static function getInstance() : Routing
    {
        if (! self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Gets matching URIs
     * @param string $uri
     * @param string $verb
     * @return ModelCollection
     * @throws ReflectionException
     */
    public static function getMatches(string $verb, string $uri) : ModelCollection
    {
        return self::getInstance()->getUriMatches($verb, $uri);
    }

    /**
     * Gets matching URIs
     * @param string $uri
     * @param string $verb
     * @return ModelCollection
     * @throws ReflectionException
     */
    public function getUriMatches(string $verb, string $uri) : ModelCollection
    {
        $matchedRoutes = new ModelCollection();

        if (! isset($this->routes[$verb])) {
            return $matchedRoutes;
        }

        foreach ($this->routes[$verb] as $route => $callback) {
            $routeRegex = str_replace('/', '\/', $route);
            preg_match('/^' . $routeRegex . '$/', $uri, $match);

            if (! $match) {
                continue;
            }

            $matchedRoutes->addModel(new MatchedRouteModel([
                'route' => $route,
                'callback' => $callback,
                'match' => $match,
            ]));
        }

        return $matchedRoutes;
    }

    /**
     * Gets the routes array
     * @return array
     */
    public static function getRoutes() : array
    {
        return self::getInstance()->getRoutesArray();
    }

    /**
     * Gets the routes array
     * @return array
     */
    public function getRoutesArray() : array
    {
        return $this->routes;
    }

    /**
     * Sets a get request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public static function get(string $uri, callable $callback) : Routing
    {
        return self::getInstance()->getRequest($uri, $callback);
    }

    /**
     * Sets a get request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public function getRequest(string $uri, callable $callback) : Routing
    {
        $this->routes['get'][$uri] = $callback;
        return $this;
    }

    /**
     * Sets a post request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public static function post(string $uri, callable $callback) : Routing
    {
        return self::getInstance()->postRequest($uri, $callback);
    }

    /**
     * Sets a post request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public function postRequest(string $uri, callable $callback) : Routing
    {
        $this->routes['post'][$uri] = $callback;
        return $this;
    }

    /**
     * Sets a put request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public static function put(string $uri, callable $callback) : Routing
    {
        return self::getInstance()->putRequest($uri, $callback);
    }

    /**
     * Sets a put request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public function putRequest(string $uri, callable $callback) : Routing
    {
        $this->routes['put'][$uri] = $callback;
        return $this;
    }

    /**
     * Sets a patch request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public static function patch(string $uri, callable $callback) : Routing
    {
        return self::getInstance()->patchRequest($uri, $callback);
    }

    /**
     * Sets a patch request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public function patchRequest(string $uri, callable $callback) : Routing
    {
        $this->routes['patch'][$uri] = $callback;
        return $this;
    }

    /**
     * Sets a delete request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public static function delete(string $uri, callable $callback) : Routing
    {
        return self::getInstance()->deleteRequest($uri, $callback);
    }

    /**
     * Sets a delete request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public function deleteRequest(string $uri, callable $callback) : Routing
    {
        $this->routes['delete'][$uri] = $callback;
        return $this;
    }

    /**
     * Sets an options request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public static function options(string $uri, callable $callback) : Routing
    {
        return self::getInstance()->optionsRequest($uri, $callback);
    }

    /**
     * Sets an options request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public function optionsRequest(string $uri, callable $callback) : Routing
    {
        $this->routes['options'][$uri] = $callback;
        return $this;
    }

    /**
     * Sets a cli request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public static function cli(string $uri, callable $callback) : Routing
    {
        return self::getInstance()->cliRequest($uri, $callback);
    }

    /**
     * Sets an options request route
     * @param string $uri
     * @param callable $callback
     * @return self
     */
    public function cliRequest(string $uri, callable $callback) : Routing
    {
        $this->routes['cli'][$uri] = $callback;
        return $this;
    }
}
