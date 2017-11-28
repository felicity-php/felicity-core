<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace tests\services\config;

use felicity\routing\Routing;
use PHPUnit\Framework\TestCase;
use felicity\datamodel\ModelCollection;
use felicity\routing\models\MatchedRouteModel;

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

        Routing::get('testing\/(.*)?', $testFunction);

        $dudMatch = $routing->getUriMatches('get', 'asdf/thing');

        self::assertInstanceOf(ModelCollection::class, $dudMatch);

        self::assertEmpty($dudMatch);

        $testFunctionMatch = $routing->getUriMatches('get', 'testing/asdf');

        self::assertCount(1, $testFunctionMatch);

        foreach ($testFunctionMatch as $model) {
            /** @var MatchedRouteModel $model */

            self::assertEquals('testing\/(.*)?', $model->route);

            self::assertEquals($testFunction, $model->callback);

            self::assertEquals(
                'testFunction',
                \call_user_func($model->callback)
            );
        }

        Routing::post('thing', [$this, 'callableFunction']);

        $newTest = Routing::getMatches('post', 'thing');

        self::assertCount(1, $newTest);

        foreach ($newTest as $model) {
            /** @var MatchedRouteModel $model */

            self::assertEquals(
                'callableFunction',
                \call_user_func($model->callback)
            );
        }
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
