<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace tests\services\request;

use PHPUnit\Framework\TestCase;
use felicity\core\services\config\Config;
use felicity\core\services\request\UriService;

/**
 * Class UriTest
 */
class UriTest extends TestCase
{
    /**
     * Test the getUriModel method
     */
    public function testGetUriModel()
    {
        $uriService = new UriService(Config::getInstance());

        $uriModel = $uriService->getUriModel('/testing/thing/');

        self::assertEquals(
            array(
                'raw' => '/testing/thing/',
                'segments' => [
                    'testing',
                    'thing',
                ],
                'path' => 'testing/thing',
                'queryRaw' => '',
                'query' => [],
                'page' => 1,
            ),
            $uriModel->asArray(true)
        );

        $uriModel = $uriService->getUriModel(
            '/testing/thing/index.php/new/route'
        );

        self::assertEquals(
            array(
                'raw' => '/testing/thing/index.php/new/route',
                'segments' => [
                    'new',
                    'route',
                ],
                'path' => 'new/route',
                'queryRaw' => '',
                'query' => [],
                'page' => 1,
            ),
            $uriModel->asArray(true)
        );

        $uriModel = $uriService->getUriModel(
            '/testing/thing/mypage.php/new/route'
        );

        self::assertEquals(
            array(
                'raw' => '/testing/thing/mypage.php/new/route',
                'segments' => [
                    'new',
                    'route',
                ],
                'path' => 'new/route',
                'queryRaw' => '',
                'query' => [],
                'page' => 1,
            ),
            $uriModel->asArray(true)
        );

        $uriModel = $uriService->getUriModel(
            'segment/another/segment-thing/?test-key=test-val&source[]=google&source[]=facebook'
        );

        self::assertEquals(
            [
                'raw' => 'segment/another/segment-thing/',
                'segments' => [
                    'segment',
                    'another',
                    'segment-thing',
                ],
                'path' => 'segment/another/segment-thing',
                'queryRaw' => 'test-key=test-val&source[]=google&source[]=facebook',
                'query' => [
                    'test-key' => 'test-val',
                    'source' => [
                        0 => 'google',
                        1 => 'facebook',
                    ],
                ],
                'page' => 1,
            ],
            $uriModel->asArray(true)
        );
    }
}
