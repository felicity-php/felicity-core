<?php

namespace felicity\core;

use Composer\Autoload\ClassLoader;

/**
 * Class Bootstrap
 */
class Bootstrap
{
    /**
     * Runs bootstrap
     * @throws \ReflectionException
     */
    public static function run()
    {
        $reflection = new \ReflectionClass(ClassLoader::class);
        $vendorPath = \dirname($reflection->getFileName(), 2);
        $vendorGlobPattern = "{$vendorPath}/*";
        $projectRoot = \dirname($vendorPath);
        $projectComposer = "{$projectRoot}/composer.json";

        if (file_exists($projectComposer)) {
            self::processComposerFile($projectComposer);
        }

        foreach (glob($vendorGlobPattern) as $dir) {
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
                    self::processComposerFile($composerJsonPath);
                }
            }
        }
    }

    /**
     * Processes a composer file
     * @param string $filePath
     */
    private static function processComposerFile(string $filePath)
    {
        if (! file_exists($filePath)) {
            return;
        }

        $json = json_decode(file_get_contents($filePath), true);

        if (! isset($json['extra']['bootstrap']) ||
            ! \is_array($json['extra']['bootstrap'])
        ) {
            return;
        }

        foreach ($json['extra']['bootstrap'] as $class => $method) {
            (new $class)->{$method}();
        }
    }
}
