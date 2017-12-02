<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\controllers;

use Exception;
use ReflectionException;
use felicity\routing\Routing;
use felicity\translate\Translate;
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
     */
    public function run(array $argv)
    {
        $argumentsModel = new ArgumentsModel();

        $argumentsModel->addRawArgs($argv);

        try {
            $this->innerRun($argumentsModel);
        } catch (Exception $e) {
            $msg = Translate::get('felicityCore', 'followingExceptionCaught');
            ConsoleOutput::write("<bold>{$msg}</bold>", 'red');
            ConsoleOutput::write($e->getMessage(), 'red');
            ConsoleOutput::write("File: {$e->getFile()}");
            ConsoleOutput::write("Line: {$e->getLine()}");
            if ($argumentsModel->getArgument('trace') !== 'true') {
                ConsoleOutput::write(
                    Translate::get('felicityCore', 'getTrace'),
                    'yellow'
                );
                return;
            }
            print_r($e->getTrace());
        }
    }

    /**
     * Internal run function we can try/catch in the public run function
     * @param ArgumentsModel $argumentsModel
     */
    private function innerRun(ArgumentsModel $argumentsModel)
    {
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

            $callable($argumentsModel);

            return;
        }

        $msg = Translate::get('felicityCore', 'commandNotFound');
        ConsoleOutput::write("<bold>{$msg}</bold>", 'red');
    }

    /**
     * Lists available commands
     */
    private function listCommands()
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

        $routes = Routing::descriptionTranslationKeys();

        if (! $routes) {
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
