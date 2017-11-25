<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\models;

use felicity\datamodel\Model;
use felicity\datamodel\services\datahandlers\IntHandler;
use felicity\datamodel\services\datahandlers\ArrayHandler;
use felicity\datamodel\services\datahandlers\StringHandler;
use felicity\datamodel\services\datahandlers\StringArrayHandler;

/**
 * Class MatchedRouteModel
 */
class UriModel extends Model
{
    /** @var string $raw */
    public $raw = '';

    /** @var array $segments */
    public $segments = [];

    /** @var string $path */
    public $path = '';

    /** @var string $queryRaw */
    public $queryRaw = '';

    /** @var array $query */
    public $query = [];

    /** @var int $page */
    public $page = 1;

    /**
     * @inheritdoc
     */
    protected function defineHandlers(): array
    {
        return [
            'raw' => [
                'class' => StringHandler::class,
            ],
            'segments' => [
                'class' => StringArrayHandler::class,
            ],
            'path' => [
                'class' => StringHandler::class,
            ],
            'queryRaw' => [
                'class' => StringHandler::class,
            ],
            'query' => [
                'class' => ArrayHandler::class,
            ],
            'page' => [
                'class' => IntHandler::class,
            ],
        ];
    }
}
