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
     * @throws ReflectionException
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

        EventManager::call('Felicity_Bootstrap_AfterRun', new EventModel([
            'sender' => $this,
        ]));

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

        $json = json_decode(file_get_contents($filePath), true);

        if (! isset($json['extra']['bootstrap']) ||
            ! \is_array($json['extra']['bootstrap'])
        ) {
            return $this;
        }

        return $this->processBootstrapArray($json['extra']['bootstrap']);
    }

    /**
     * Processes bootstrap array
     * @param array $bootstrapArray
     * @return self
     */
    public function processBootstrapArray(array $bootstrapArray) : Bootstrap
    {
        foreach ($bootstrapArray as $bootstrapArrayItem) {
            $this->processBootstrapArrayItem($bootstrapArrayItem);
        }

        return $this;
    }

    /**
     * Processes a bootstrap array item
     * @param array $bootstrapArrayItem
     */
    private function processBootstrapArrayItem(array $bootstrapArrayItem)
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
                $this->processBootstrapFile($bootstrapArrayItem);
                break;
            case 'directory':
                $this->processBootstrapDirectory($bootstrapArrayItem);
                break;
            case 'directoryRecursive':
                $this->processBootstrapDirectoryRecursive($bootstrapArrayItem);
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

        (new $bootstrapArrayItem['class'])->{$bootstrapArrayItem['method']}();
    }

    /**
     * Processes a bootstrap array item type of file
     * @param array $bootstrapArrayItem
     */
    private function processBootstrapFile(array $bootstrapArrayItem)
    {
        if (! isset($bootstrapArrayItem['filePath'])) {
            return;
        }

        include "{$this->projectRoot}/{$bootstrapArrayItem['filePath']}";
    }

    /**
     * Processes a bootstrap array item type of directory
     * @param array $bootstrapArrayItem
     */
    private function processBootstrapDirectory(array $bootstrapArrayItem)
    {
        if (! isset($bootstrapArrayItem['directoryPath'])) {
            return;
        }

        $dir = rtrim($bootstrapArrayItem['directoryPath'], '/');

        foreach (glob($this->projectRoot . "/{$dir}/*") as $file) {
            if (! is_file($file) || is_dir($file)) {
                continue;
            }

            $pathInfo = pathinfo($file);

            if (! isset($pathInfo['extension']) ||
                $pathInfo['extension'] !== 'php'
            ) {
                continue;
            }

            include $file;
        }
    }

    /**
     * Processes a bootstrap array item type of directoryRecursive
     * @param array $bootstrapArrayItem
     */
    private function processBootstrapDirectoryRecursive(
        array $bootstrapArrayItem
    ) {
        if (! isset($bootstrapArrayItem['directoryPath'])) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->projectRoot . '/' . $bootstrapArrayItem['directoryPath']
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

            include $item[0];
        }
    }
}
