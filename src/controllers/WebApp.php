<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\controllers;

use Exception;
use ReflectionException;
use felicity\logging\Logger;
use felicity\core\FelicityCore;
use felicity\events\EventManager;
use felicity\events\models\EventModel;
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
        Logger::log(
            'Starting WebApp run...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        EventManager::call('Felicity_WebApp_BeforeRun', new EventModel([
            'sender' => $this,
        ]));

        ob_start();

        try {
            $this->innerRun();
        } catch (Exception $e) {
            Logger::log(
                'WebApp Exception caught, processing...',
                Logger::LEVEL_WARNING,
                'felicityCore'
            );

            $eventModel = new EventModel([
                'sender' => $this,
                'params' => [
                    'exception' => $e,
                ],
            ]);

            EventManager::call('Felicity_WebApp_Exception', $eventModel);

            if (! $eventModel->performAction) {
                return;
            }

            if ($e instanceof HttpException) {
                Logger::log(
                    "WebApp Exception is an HttpException: {$e->getCode()}",
                    Logger::LEVEL_WARNING,
                    'felicityCore'
                );

                $e->render();

                return;
            }

            if (class_exists('Twig_Error_Runtime') &&
                $e instanceof \Twig_Error_Runtime
            ) {
                $previous = $e->getPrevious();

                if ($previous instanceof HttpException) {
                    Logger::log(
                        "WebApp Exception is an HttpException: {$e->getCode()}",
                        Logger::LEVEL_WARNING,
                        'felicityCore'
                    );

                    $previous->render();

                    return;
                }
            }

            $msg = $e->getMessage();

            Logger::log(
                "WebApp Exception caught: {$msg}",
                Logger::LEVEL_ERROR,
                'felicityCore'
            );

            Logger::log(
                "WebApp Exception file: {$e->getFile()}",
                Logger::LEVEL_ERROR,
                'felicityCore'
            );

            Logger::log(
                "WebApp Exception line: {$e->getLine()}",
                Logger::LEVEL_ERROR,
                'felicityCore'
            );

            Logger::log(
                'ConsoleApp trace: ' . print_r($e->getTrace(), true),
                Logger::LEVEL_ERROR,
                'felicityCore'
            );

            (new HttpException($msg, $e->getCode()))->render();
        }

        ob_end_flush();

        EventManager::call('Felicity_WebApp_AfterRun', new EventModel([
            'sender' => $this,
        ]));
    }

    /**
     * Internal run function so we can try/catch
     * @throws ReflectionException
     * @throws HttpException
     */
    private function innerRun()
    {
        Logger::log(
            'WebApp matching route...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        $routingModel = FelicityCore::getRoutingService()->runUri(
            FelicityCore::getUriModel()
        );

        EventManager::call('Felicity_WebApp_AfterRouteMatching', new EventModel([
            'sender' => $this,
        ]));

        if ($routingModel->responseCode !== 200) {
            throw new HttpException(
                $routingModel->errorMessage,
                $routingModel->responseCode
            );
        }

        $hasOutput = false;

        if (\is_array($routingModel->responseData)) {
            Logger::log(
                'WebApp routing response is an array, sending json response...',
                Logger::LEVEL_INFO,
                'felicityCore'
            );

            header('Content-type:application/json;charset=utf-8');

            echo(json_encode($routingModel->responseData));

            $hasOutput = true;
        }

        if (\is_string($routingModel->responseData)) {
            Logger::log(
                'WebApp routing response is a string, sending response...',
                Logger::LEVEL_INFO,
                'felicityCore'
            );

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
