<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core;

use RegexIterator;
use ReflectionClass;
use ReflectionException;
use RecursiveRegexIterator;
use felicity\logging\Logger;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use felicity\events\EventManager;
use Composer\Autoload\ClassLoader;
use felicity\events\models\EventModel;

/**
 * Class Bootstrap
 */
class Bootstrap
{
    /** @var string $projectRoot */
    private $projectRoot;

    /**
     * Bootstrap constructor
     */
    public function __construct()
    {
        $this->projectRoot = \dirname(
            (new ReflectionClass(ClassLoader::class))->getFileName(),
            3
        );
    }

    /**
     * Runs bootstrap
     * @return self
     * @throws ReflectionException
     */
    public function run() : Bootstrap
    {

        Logger::log(
            'Starting bootstrap run...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        Logger::log(
            'Calling event `Felicity_Bootstrap_BeforeRun`...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        EventManager::call('Felicity_Bootstrap_BeforeRun', new EventModel([
            'sender' => $this,
        ]));

        $projectComposer = "{$this->projectRoot}/composer.json";

        if (file_exists($projectComposer)) {
            $this->processComposerFile($projectComposer);
        }

        if (! is_dir("$this->projectRoot/vendor")) {
            return $this;
        }

        foreach (glob("$this->projectRoot/vendor/*") as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            $globPattern = "{$dir}/*";

            foreach (glob($globPattern) as $vendorDir) {
                if (! is_dir($vendorDir)) {
                    continue;
                }

                $composerJsonPath = "{$vendorDir}/composer.json";

                if (file_exists($projectComposer)) {
                    $this->processComposerFile($composerJsonPath);
                }
            }
        }

        Logger::log(
            'Calling event `Felicity_Bootstrap_AfterRun`...',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        EventManager::call('Felicity_Bootstrap_AfterRun', new EventModel([
            'sender' => $this,
        ]));

        Logger::log(
            'Bootstrap run finished',
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        return $this;
    }

    /**
     * Processes a composer file
     * @param string $filePath
     * @return self
     */
    public function processComposerFile(string $filePath) : Bootstrap
    {
        if (! file_exists($filePath)) {
            return $this;
        }

        Logger::log(
            "Processing composer file {$filePath}",
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        $json = json_decode(file_get_contents($filePath), true);

        if (! isset($json['extra']['bootstrap']) ||
            ! \is_array($json['extra']['bootstrap'])
        ) {
            return $this;
        }

        return $this->processBootstrapArray(
            $json['extra']['bootstrap'],
            \dirname($filePath)
        );
    }

    /**
     * Processes bootstrap array
     * @param array $bootstrapArray
     * @param string|null $root
     * @return self
     */
    public function processBootstrapArray(array $bootstrapArray, $root = null) : Bootstrap
    {
        foreach ($bootstrapArray as $bootstrapArrayItem) {
            $this->processBootstrapArrayItem($bootstrapArrayItem, $root);
        }

        return $this;
    }

    /**
     * Processes a bootstrap array item
     * @param array $bootstrapArrayItem
     * @param string|null $root
     */
    private function processBootstrapArrayItem(array $bootstrapArrayItem, $root = null)
    {
        if (! isset($bootstrapArrayItem['type'])) {
            return;
        }

        if (isset($bootstrapArrayItem['requestType'])) {
            if (! \defined('REQUEST_TYPE') ||
                $bootstrapArrayItem['requestType'] !== REQUEST_TYPE
            ) {
                return;
            }

            if ($bootstrapArrayItem['requestType'] !== REQUEST_TYPE) {
                return;
            }
        }

        switch ($bootstrapArrayItem['type']) {
            case 'classMethod':
                $this->processBootstrapClassMethod($bootstrapArrayItem);
                break;
            case 'file':
                $this->processBootstrapFile($bootstrapArrayItem, $root);
                break;
            case 'directory':
                $this->processBootstrapDirectory($bootstrapArrayItem, $root);
                break;
            case 'directoryRecursive':
                $this->processBootstrapDirectoryRecursive($bootstrapArrayItem, $root);
        }
    }

    /**
     * Processes a bootstrap array item type of classMethod
     * @param array $bootstrapArrayItem
     */
    private function processBootstrapClassMethod(array $bootstrapArrayItem)
    {
        if (! isset(
            $bootstrapArrayItem['class'],
            $bootstrapArrayItem['method']
        )) {
            return;
        }

        Logger::log(
            'Processing composer bootstrap class method ' .
                var_export($bootstrapArrayItem, true),
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        (new $bootstrapArrayItem['class'])->{$bootstrapArrayItem['method']}();
    }

    /**
     * Processes a bootstrap array item type of file
     * @param array $bootstrapArrayItem
     * @param string|null $root
     */
    private function processBootstrapFile(array $bootstrapArrayItem, $root = null)
    {
        if (! isset($bootstrapArrayItem['filePath'])) {
            return;
        }

        $root = $root ?? $this->projectRoot;

        Logger::log(
            "Processing composer bootstrap file {$root}",
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        include "{$root}/{$bootstrapArrayItem['filePath']}";
    }

    /**
     * Processes a bootstrap array item type of directory
     * @param array $bootstrapArrayItem
     * @param string|null $root
     */
    private function processBootstrapDirectory(array $bootstrapArrayItem, $root = null)
    {
        if (! isset($bootstrapArrayItem['directoryPath'])) {
            return;
        }

        $root = $root ?? $this->projectRoot;

        $dir = rtrim($bootstrapArrayItem['directoryPath'], '/');

        Logger::log(
            "Processing composer bootstrap directory {$dir}",
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        foreach (glob($root . "/{$dir}/*") as $file) {
            if (! is_file($file) || is_dir($file)) {
                continue;
            }

            $pathInfo = pathinfo($file);

            if (! isset($pathInfo['extension']) ||
                $pathInfo['extension'] !== 'php'
            ) {
                continue;
            }

            Logger::log(
                "Processing composer bootstrap directory file {$file}",
                Logger::LEVEL_INFO,
                'felicityCore'
            );

            include $file;
        }
    }

    /**
     * Processes a bootstrap array item type of directoryRecursive
     * @param array $bootstrapArrayItem
     * @param string|null $root
     */
    private function processBootstrapDirectoryRecursive(
        array $bootstrapArrayItem,
        $root = null
    ) {
        if (! isset($bootstrapArrayItem['directoryPath'])) {
            return;
        }

        $root = $root ?? $this->projectRoot;

        Logger::log(
            'Processing composer bootstrap directory recursive ' .
                $root . '/' . $bootstrapArrayItem['directoryPath'],
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $root . '/' . $bootstrapArrayItem['directoryPath']
            )
        );

        $regex = new RegexIterator(
            $iterator,
            '/^.+\.php$/i',
            RecursiveRegexIterator::GET_MATCH
        );

        foreach ($regex as $item) {
            if (! isset($item[0]) || ! is_file($item[0])) {
                continue;
            }

            Logger::log(
                "Processing composer bootstrap recursive directory file {$item[0]}",
                Logger::LEVEL_INFO,
                'felicityCore'
            );

            include $item[0];
        }
    }
}
