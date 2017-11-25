<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\models;

use felicity\datamodel\Model;
use felicity\datamodel\services\datahandlers\ArrayHandler;
use felicity\datamodel\services\datahandlers\StringHandler;

/**
 * Class MatchedRouteModel
 */
class MatchedRouteModel extends Model
{
    /** @var string $route */
    public $route;

    /** @var callable $callback */
    public $callback;

    /** @var array $match */
    public $match;

    /**
     * @inheritdoc
     */
    protected function defineHandlers(): array
    {
        return [
            'route' => [
                'class' => StringHandler::class,
            ],
            'match' => [
                'class' => ArrayHandler::class,
            ],
        ];
    }
}
