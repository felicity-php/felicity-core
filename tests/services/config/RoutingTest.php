<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace tests\services\config;

use PHPUnit\Framework\TestCase;
use felicity\core\services\config\Routing;

/**
 * Class ConfigTest
 */
class RoutingTest extends TestCase
{
    /**
     * Tests routing
     */
    public function testRouting()
    {
        $testFunction = function () : string {
            return 'testFunction';
        };

        $routing = Routing::getInstance();

        Routing::get('testing/(.*)?', $testFunction);

        $dudMatch = $routing->getUriMatches('get', 'asdf/thing');

        self::assertInternalType('array', $dudMatch);

        self::assertEmpty($dudMatch);

        $testFunctionMatch = $routing->getUriMatches('get', 'testing/asdf');

        self::assertEquals('testing/(.*)?', $testFunctionMatch[0]['route']);

        self::assertEquals($testFunction, $testFunctionMatch[0]['callback']);

        self::assertEquals('testFunction', $testFunctionMatch[0]['callback']());

        Routing::post('thing', [
            $this,
            'callableFunction'
        ]);

        $newTest = Routing::getMatches('post', 'thing');

        self::assertEquals(
            'callableFunction',
            \call_user_func($newTest[0]['callback'])
        );
    }

    /**
     * A test function callable
     * @return string
     */
    public function callableFunction() : string
    {
        return 'callableFunction';
    }
}
