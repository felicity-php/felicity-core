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

            include $item[0];
        }
    }
}
