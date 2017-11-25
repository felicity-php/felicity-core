<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\controllers;

use ReflectionException;
use felicity\core\FelicityCore;

/**
 * Class WebApp
 */
class WebApp
{
    /**
     * Runs the application
     * @throws ReflectionException
     */
    public function run()
    {
        ob_start();

        $routingModel = FelicityCore::getRoutingService()->runUri(
            FelicityCore::getUriModel()
        );

        if ($routingModel->responseCode !== 200) {
            // TODO: throw to an http error thrower or something
            var_dump('TODO');
            die;
        }

        $hasOutput = false;

        if (\is_array($routingModel->responseData)) {
            header('Content-type:application/json;charset=utf-8');
            echo(json_encode($routingModel->responseData));
            $hasOutput = true;
        }

        if (\is_string($routingModel->responseData)) {
            echo($routingModel->responseData);
            $hasOutput = true;
        }

        if (! $hasOutput) {
            // TODO: throw to an http 404 error thrower or something
            var_dump('TODO');
            die;
        }

        ob_end_flush();
    }
}
