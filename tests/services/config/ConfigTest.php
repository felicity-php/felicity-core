<?php

namespace tests\services\config;

use PHPUnit\Framework\TestCase;
use felicity\core\services\config\Config;

/**
 * Class ConfigTest
 */
class ConfigTest extends TestCase
{
    /**
     * Tests config setting and getting
     */
    public function testConfig()
    {
        Config::$instance->set('testing', 'whatever')
            ->set('thing.stuff.test1', 'asdf')
            ->set('thing.stuff.test2', 'qwerty')
            ->set('thing.stuff.test2', 'new test')
            ->set('thing.stuff.test3', [
                'arrayTest' => true,
            ]);

        self::assertNull(Config::$instance->get('stuff'));

        self::assertInternalType(
            'array',
            Config::$instance->get('thing.stuff')
        );

        self::assertEquals(
            Config::$instance->get('thing.stuff.test1'),
            'asdf'
        );

        self::assertEquals(
            Config::$instance->get('thing.stuff.test2'),
            'new test'
        );

        self::assertEquals(
            Config::$instance->get('thing.stuff.test3'),
            [
                'arrayTest' => true,
            ]
        );
    }
}
