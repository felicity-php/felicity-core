<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\models;

use felicity\datamodel\Model;
use felicity\datamodel\services\datahandlers\IntHandler;
use felicity\datamodel\services\datahandlers\BoolHandler;
use felicity\datamodel\services\datahandlers\StringHandler;

/**
 * Class RoutingModel
 */
class RoutingModel extends Model
{
    /** @var int $responseCode */
    public $responseCode = 200;

    /** @var string $errorMessage */
    public $errorMessage = '';

    /** @var bool $stopRouting */
    public $stopRouting = false;

    /** @var mixed $responseData */
    public $responseData;

    /**
     * @inheritdoc
     */
    protected function defineHandlers(): array
    {
        return [
            'responseCode' => [
                'class' => IntHandler::class,
            ],
            'errorMessage' => [
                'class' => StringHandler::class,
            ],
            'stopRouting' => [
                'class' => BoolHandler::class,
            ],
        ];
    }
}
