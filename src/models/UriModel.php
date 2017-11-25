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

    /** @var string $requestMethod */
    public $requestMethod = 'get';

    /**
     * Gets the specified segment
     * @param int $segment
     * @return string|null
     */
    public function getSegment(int $segment)
    {
        $segment--;
        return $this->segments[$segment] ?? null;
    }

    /**
     * Gets the specified segment
     * @param int $segment
     * @return string|null
     */
    public function getSegmentZeroIndex(int $segment)
    {
        return $this->segments[$segment] ?? null;
    }

    /**
     * Gets the last segment
     * @return string
     */
    public function lastSegment() : string
    {
        return $this->segments ?
            $this->segments[\count($this->segments) - 1] :
            '';
    }

    /**
     * Gets query item with dot syntax
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getQueryItem(string $key, $default = null)
    {
        return $this->getArrayDot($this->query, $key) ?: $default;
    }

    /**
     * Gets an array item from dot syntax
     * @param array $arr
     * @param string $path
     * @return mixed
     */
    private function getArrayDot(array $arr, string $path)
    {
        $val = $arr;

        foreach (explode('.', $path) as $step) {
            if (! isset($val[$step])) {
                return null;
            }

            $val = $val[$step];
        }

        return $val;
    }

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
            'requestMethod' => [
                'class' => StringHandler::class,
            ],
        ];
    }
}
