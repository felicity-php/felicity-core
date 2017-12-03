<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\controllers;

use Exception;
use felicity\logging\Logger;
use felicity\routing\Routing;
use felicity\translate\Translate;
use felicity\events\EventManager;
use felicity\events\models\EventModel;
use felicity\core\models\ArgumentsModel;
use felicity\consoleoutput\ConsoleOutput;

/**
 * Class ConsoleApp
 */
class ConsoleApp
{
    /**
     * Runs the application
     * @param array $argv
     * @throws \ReflectionException
     */
    public function run(array $argv)
    {
        Logger::log(
            'Starting ConsoleApp run...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        Logger::log(
            'Calling event `Felicity_ConsoleApp_BeforeRun`...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        Logger::log(
            'Calling event `Felicity_ConsoleApp_BeforeRun`...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        $argumentsModel = new ArgumentsModel();

        $argumentsModel->addRawArgs($argv);

        EventManager::call('Felicity_ConsoleApp_BeforeRun', new EventModel([
            'sender' => $this,
            'params' => [
                'argumentsModel' => $argumentsModel,
            ]
        ]));

        try {
            $this->innerRun($argumentsModel);
        } catch (Exception $e) {
            Logger::log(
                'ConsoleApp Exception caught, processing...',
                Logger::LEVEL_WARNING,
                'felicityCore'
            );

            $eventModel = new EventModel([
                'sender' => $this,
                'params' => [
                    'exception' => $e,
                ],
            ]);

            EventManager::call('Felicity_ConsoleApp_Exception', $eventModel);

            if (! $eventModel->performAction) {
                return;
            }

            $trans = Translate::get('felicityCore', 'followingExceptionCaught');
            ConsoleOutput::write("<bold>{$trans}</bold>", 'red');

            $msg = $e->getMessage();
            Logger::log(
                "ConsoleApp Exception caught: {$msg}",
                Logger::LEVEL_ERROR,
                'felicityCore'
            );
            ConsoleOutput::write($msg, 'red');

            $file = $e->getFile();
            Logger::log(
                "ConsoleApp Exception file: {$file}",
                Logger::LEVEL_ERROR,
                'felicityCore'
            );
            ConsoleOutput::write("File: {$file}");

            $line = $e->getLine();
            Logger::log(
                "ConsoleApp Exception line: {$line}",
                Logger::LEVEL_ERROR,
                'felicityCore'
            );
            ConsoleOutput::write("Line: {$line}");

            Logger::log(
                'ConsoleApp trace: ' . print_r($e->getTrace(), true),
                Logger::LEVEL_ERROR,
                'felicityCore'
            );

            if ($argumentsModel->getArgument('trace') !== 'true') {
                ConsoleOutput::write(
                    Translate::get('felicityCore', 'getTrace'),
                    'yellow'
                );
                return;
            }

            print_r($e->getTrace());
        }

        Logger::log(
            'Calling event `Felicity_ConsoleApp_AfterRun`...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        EventManager::call('Felicity_ConsoleApp_AfterRun', new EventModel([
            'sender' => $this
        ]));
    }

    /**
     * Internal run function we can try/catch in the public run function
     * @param ArgumentsModel $argumentsModel
     */
    private function innerRun(ArgumentsModel $argumentsModel)
    {
        Logger::log(
            'ConsoleApp attempting to set unlimited time limit...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        // Prevent timeout (hopefully)
        @set_time_limit(0);

        if (! $argumentsModel->route) {
            $this->listCommands();
            return;
        }

        $routes = Routing::getRoutes();

        if (! isset($routes['cli'])) {
            $this->showNoRoutes();
        }

        foreach ($routes['cli'] as $route => $callable) {
            if ($route !== $argumentsModel->route) {
                continue;
            }

            Logger::log(
                "ConsoleApp matched route {$route}, running specified method...",
                Logger::LEVEL_INFO,
                'felicityCore'
            );

            $callable($argumentsModel);

            return;
        }

        Logger::log(
            "ConsoleApp route {$argumentsModel->route} not found",
            Logger::LEVEL_WARNING,
            'felicityCore'
        );

        $msg = Translate::get('felicityCore', 'commandNotFound');
        ConsoleOutput::write("<bold>{$msg}</bold>", 'red');
    }

    /**
     * Lists available commands
     */
    private function listCommands()
    {
        Logger::log(
            'ConsoleApp listing available commands',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        ConsoleOutput::write('');

        ConsoleOutput::write(
            Translate::get('felicityCore', 'felicityCommandLine') . ' ',
            'green'
        );

        ConsoleOutput::write('');

        ConsoleOutput::write(
            Translate::get('felicityCore', 'usage:') . ' ',
            'yellow',
            false
        );

        ConsoleOutput::write(Translate::get('felicityCore', 'usageExample'));

        ConsoleOutput::write('');

        $routes = Routing::descriptionTranslationKeys();

        if (! $routes) {
            Logger::log(
                'ConsoleApp no CLI commands available',
                Logger::LEVEL_INFO,
                'felicityCore'
            );

            ConsoleOutput::write(
                Translate::get('felicityCore', 'noCliCommands'),
                'yellow'
            );

            ConsoleOutput::write('');

            return;
        }

        ksort($routes);

        $toCharacters = 0;

        foreach (array_keys($routes) as $route) {
            $len = \strlen($route);
            $toCharacters = $len > $toCharacters ? $len : $toCharacters;
        }

        $toCharacters += 2;

        foreach ($routes as $route => $desc) {
            $len = \strlen($route);
            $to = abs($len - $toCharacters);

            ConsoleOutput::write($route, 'green', false);

            if (! $desc) {
                continue;
            }

            ConsoleOutput::write(
                str_repeat(' ', $to) .
                    Translate::get($desc['category'], $desc['key']),
                null,
                false
            );

            ConsoleOutput::write('');
        }

        ConsoleOutput::write('');
    }

    private function showNoRoutes()
    {
        ConsoleOutput::write('');

        ConsoleOutput::write(
            Translate::get('felicityCore', 'felicityCommandLine') . ' ',
            'green'
        );

        ConsoleOutput::write('');

        ConsoleOutput::write(
            Translate::get('felicityCore', 'usage:') . ' ',
            'yellow',
            false
        );

        ConsoleOutput::write(Translate::get('felicityCore', 'usageExample'));

        ConsoleOutput::write('');

        ConsoleOutput::write(
            Translate::get('felicityCore', 'noCliCommands'),
            'yellow'
        );

        ConsoleOutput::write('');
    }
}
