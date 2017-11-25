<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace tests\services\request;

use voku\helper\AntiXSS;
use PHPUnit\Framework\TestCase;
use felicity\core\services\config\Config;
use felicity\core\services\request\UriService;

/**
 * Class UriTest
 */
class UriServiceTest extends TestCase
{
    /**
     * Test the getUriModel method
     */
    public function testGetUriModel()
    {
        $uriService = new UriService(
            Config::getInstance(),
            new AntiXSS()
        );

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
                'requestMethod' => 'get',
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
                'requestMethod' => 'get',
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
                'requestMethod' => 'get',
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
                        'google',
                        'facebook',
                    ],
                ],
                'page' => 1,
                'requestMethod' => 'get',
            ],
            $uriModel->asArray(true)
        );

        self::assertCount(3, $uriModel->segments);

        self::assertEquals('test-val', $uriModel->getQueryItem('test-key'));

        self::assertEquals('facebook', $uriModel->getQueryItem('source.1'));

        self::assertEquals('segment-thing', $uriModel->getSegment(3));

        self::assertEquals('segment-thing', $uriModel->getSegmentZeroIndex(2));

        self::assertEquals('segment-thing', $uriModel->lastSegment());

        $uriModel = $uriService->getUriModel(
            'segment/another/page/2'
        );

        self::assertEquals('segment/another', $uriModel->path);

        self::assertEquals('segment/another/page/2', $uriModel->raw);

        self::assertEquals(2, $uriModel->page);
    }
}
