<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

(new \felicity\core\Bootstrap)->run()
    ->processComposerFile(
        __DIR__ . '/testComposerBootstrap.json'
    );
