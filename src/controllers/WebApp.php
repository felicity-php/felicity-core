<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\controllers;

use Exception;
use ReflectionException;
use felicity\core\FelicityCore;
use felicity\core\exceptions\HttpException;

/**
 * Class WebApp
 */
class WebApp
{
    /**
     * Runs the application
     * @throws ReflectionException
     * @throws HttpException
     */
    public function run()
    {
        ob_start();

        try {
            $this->innerRun();
        } catch (Exception $e) {
            if ($e instanceof HttpException) {
                $e->render();
                return;
            }

            (new HttpException($e->getMessage(), $e->getCode()))->render();
        }

        ob_end_flush();
    }

    /**
     * Runs internal run function so we can try/catch
     * @throws ReflectionException
     * @throws HttpException
     */
    private function innerRun()
    {
        $routingModel = FelicityCore::getRoutingService()->runUri(
            FelicityCore::getUriModel()
        );

        if ($routingModel->responseCode !== 200) {
            throw new HttpException(
                $routingModel->errorMessage,
                $routingModel->responseCode
            );
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
            throw new HttpException(
                $routingModel->errorMessage,
                $routingModel->responseCode
            );
        }
    }
}
